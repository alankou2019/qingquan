<?php
namespace ScshuxCms\Frontend\Controller;

use Phalcon\Di\FactoryDefault;
use ScshuxCms\Core\Controller\FrontendBaseController;
use ScshuxCms\Core\Helper;
use ScshuxCms\Core\Helper\Utils;
use ScshuxCms\Dacang\Helper\WecomClient;
use ScshuxCms\Dacang\Helper\WecomCrypt;
use ScshuxCms\Dacang\Helper\WecomSyncService;
use ScshuxCms\Dacang\Model\CompanyModel;
use ScshuxCms\Dacang\Model\PlatformEventLogModel;
use ScshuxCms\Dacang\Model\PlatformIntegrationModel;
use ScshuxCms\Dacang\Model\PlatformUserIdentityModel;

class WecomController extends FrontendBaseController
{
    public function initialize()
    {
        $mainview = $this->getView()->getMainView();
        $this->getView()->setMainView(str_replace('/main', '/bs', $mainview));
    }

    public function entryAction()
    {
        $this->assertFeatureEnabled();
        $entry = $this->parseEntryPath();
        $hashKey = $entry['hash_key'];
        $company = CompanyModel::findFirst("hash_key='" . addslashes($hashKey) . "'");
        if (!$company || intval($company->status) === 0) {
            Utils::showFrontMsg('企业不存在或尚未启用');
        }
        $integration = PlatformIntegrationModel::getByCompany($company->id, 'wecom', true);
        if (!$integration) {
            Utils::showFrontMsg('企业微信应用尚未配置');
        }

        $target = $entry['target'];
        if (!$this->isSafeTarget($target)) {
            $target = $this->request->get('target');
        }
        if (!$this->isSafeTarget($target)) {
            $target = '/bs/newindex';
        }
        $state = bin2hex(openssl_random_pseudo_bytes(16));
        $this->session->set('wecom_oauth_state', $state);
        $this->session->set('wecom_company_id', intval($company->id));
        $this->session->set('wecom_company_hash_key', (string)$company->hash_key);
        $this->session->set('wecom_target', $target);

        $config = FactoryDefault::getDefault()->get('config');
        $callback = rtrim((string)$config->wecom->base_url, '/') . '/wecom/oauthcallback';
        $url = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid=' . rawurlencode($integration->corp_id) .
            '&redirect_uri=' . rawurlencode($callback) .
            '&response_type=code&scope=snsapi_base&agentid=' . rawurlencode($integration->agent_id) .
            '&state=' . rawurlencode($state) . '#wechat_redirect';
        $this->response->redirect($url, true);
    }

    public function oauthCallbackAction()
    {
        $this->assertFeatureEnabled();
        $state = (string)$this->request->get('state');
        $expected = (string)$this->session->get('wecom_oauth_state');
        if ($state === '' || $expected === '' || !hash_equals($expected, $state)) {
            Utils::showFrontMsg('企业微信登录状态校验失败，请重新进入应用');
        }
        $companyId = intval($this->session->get('wecom_company_id'));
        $integration = PlatformIntegrationModel::getByCompany($companyId, 'wecom', true);
        if (!$integration) {
            Utils::showFrontMsg('企业微信应用未启用');
        }
        try {
            $result = (new WecomClient($integration))->getUserByCode(trim($this->request->get('code')));
        } catch (\Exception $e) {
            Utils::showFrontMsg('企业微信登录失败：' . $e->getMessage());
        }
        $externalUserId = '';
        foreach (array('UserId', 'userid', 'user_id') as $userIdKey) {
            if (!empty($result[$userIdKey])) {
                $externalUserId = (string)$result[$userIdKey];
                break;
            }
        }
        if ($externalUserId === '') {
            Utils::showFrontMsg('企业微信未返回内部成员身份，请确认已切换到当前企业后重新进入应用');
        }
        $identity = PlatformUserIdentityModel::getByExternalId($companyId, 'wecom', $externalUserId);
        if (!$identity || intval($identity->status) !== 1) {
            Utils::showFrontMsg('账号尚未同步，请联系管理员同步企业微信通讯录');
        }
        $companyHashKey = (string)$this->session->get('wecom_company_hash_key');
        $this->session->set('user_id', intval($identity->company_user_id));
        $this->session->set('company_id', $companyId);
        $this->session->set('company_haskey', $companyHashKey);
        $this->session->set('current_company_haskey', $companyHashKey);
        $this->session->set('login_platform', 'wecom');
        $this->session->remove('wecom_oauth_state');

        $target = $this->session->get('wecom_target');
        if (!$this->isSafeTarget($target)) {
            $target = '/bs/newindex';
        }
        $this->response->redirect($target);
    }

    public function eventAction()
    {
        $this->assertFeatureEnabled();
        $hashKey = $this->getPathValue('/wecom/event/');
        $company = CompanyModel::findFirst("hash_key='" . addslashes($hashKey) . "'");
        if (!$company) {
            $this->response->setStatusCode(404, 'Not Found')->send();
            exit;
        }
        $integration = PlatformIntegrationModel::getByCompany($company->id, 'wecom', true);
        if (!$integration || !$integration->callback_token || !$integration->encoding_aes_key) {
            $this->response->setStatusCode(403, 'Forbidden')->send();
            exit;
        }
        $signature = $this->request->get('msg_signature');
        $timestamp = $this->request->get('timestamp');
        $nonce = $this->request->get('nonce');
        $crypt = new WecomCrypt($integration->callback_token, $integration->encoding_aes_key, $integration->corp_id);

        if ($this->request->isGet()) {
            $echo = $this->request->get('echostr');
            if (!$crypt->verifySignature($signature, $timestamp, $nonce, $echo)) {
                $this->response->setStatusCode(403, 'Forbidden')->send();
                exit;
            }
            echo $crypt->decrypt($echo);
            exit;
        }

        $raw = file_get_contents('php://input');
        $outer = simplexml_load_string($raw, 'SimpleXMLElement', LIBXML_NOCDATA | LIBXML_NONET);
        $encrypted = $outer && isset($outer->Encrypt) ? (string)$outer->Encrypt : '';
        if ($encrypted === '' || !$crypt->verifySignature($signature, $timestamp, $nonce, $encrypted)) {
            $this->response->setStatusCode(403, 'Forbidden')->send();
            exit;
        }
        $plainXml = $crypt->decrypt($encrypted);
        $event = simplexml_load_string($plainXml, 'SimpleXMLElement', LIBXML_NOCDATA | LIBXML_NONET);
        $eventType = $event && isset($event->ChangeType) ? (string)$event->ChangeType : ($event && isset($event->Event) ? (string)$event->Event : 'unknown');
        $eventKey = sha1($company->id . '|' . $timestamp . '|' . $nonce . '|' . $encrypted);
        $log = PlatformEventLogModel::findFirst("company_id=" . intval($company->id) . " and platform='wecom' and event_key='" . $eventKey . "'");
        if (!$log) {
            $log = new PlatformEventLogModel();
            $log->company_id = $company->id;
            $log->platform = 'wecom';
            $log->event_key = $eventKey;
            $log->event_type = $eventType;
            $log->status = 'received';
            $log->payload = $plainXml;
            $log->received_at = time();
            $log->save();
        }
        echo 'success';
        if (function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
        }
        if (strpos($eventType, 'user') !== false || strpos($eventType, 'party') !== false) {
            try {
                (new WecomSyncService($integration))->syncAll();
                $log->status = 'processed';
                $log->save();
            } catch (\Exception $e) {
                $integration->last_error = $e->getMessage();
                $integration->updated_at = time();
                $integration->save();
            }
        }
        exit;
    }

    private function assertFeatureEnabled()
    {
        $config = FactoryDefault::getDefault()->get('config');
        if (!isset($config->wecom) || (string)$config->wecom->enabled !== 'true') {
            $this->response->setStatusCode(404, 'Not Found')->send();
            exit;
        }
    }

    private function parseEntryPath()
    {
        $pathValue = $this->getPathValue('/wecom/entry/');
        $parts = explode('/', trim($pathValue, '/'));
        $hashKey = isset($parts[0]) ? $parts[0] : '';
        $target = '';
        if (isset($parts[1], $parts[2]) && $parts[1] === 't') {
            $target = $this->decodeTargetToken($parts[2]);
        }
        return array('hash_key' => $hashKey, 'target' => $target);
    }

    private function decodeTargetToken($token)
    {
        if (!is_string($token) || $token === '') {
            return '';
        }
        $data = strtr($token, '-_', '+/');
        $padding = strlen($data) % 4;
        if ($padding) {
            $data .= str_repeat('=', 4 - $padding);
        }
        $target = base64_decode($data, true);
        return $target === false ? '' : $target;
    }

    private function getPathValue($prefix)
    {
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        return trim(str_replace($prefix, '', $path), '/');
    }

    private function isSafeTarget($target)
    {
        return is_string($target) && strlen($target) > 0 && $target[0] === '/' && strpos($target, '//') !== 0;
    }
}
