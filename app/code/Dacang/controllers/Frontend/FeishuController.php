<?php
namespace ScshuxCms\Frontend\Controller;

use Phalcon\Di\FactoryDefault;
use ScshuxCms\Core\Controller\FrontendBaseController;
use ScshuxCms\Core\Helper\Utils;
use ScshuxCms\Dacang\Helper\FeishuClient;
use ScshuxCms\Dacang\Helper\FeishuEventCrypt;
use ScshuxCms\Dacang\Helper\FeishuSyncService;
use ScshuxCms\Dacang\Model\CompanyModel;
use ScshuxCms\Dacang\Model\PlatformEventLogModel;
use ScshuxCms\Dacang\Model\PlatformIntegrationModel;
use ScshuxCms\Dacang\Model\PlatformUserIdentityModel;

class FeishuController extends FrontendBaseController
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
        $company = CompanyModel::findFirst(
            "hash_key='" . addslashes($entry['hash_key']) . "'"
        );
        if (!$company || intval($company->status) === 0) {
            Utils::showFrontMsg('企业不存在或尚未启用');
        }
        $integration = PlatformIntegrationModel::getByCompany($company->id, 'feishu', true);
        if (!$integration) {
            Utils::showFrontMsg('飞书应用尚未配置');
        }

        $target = $entry['target'];
        if (!$this->isSafeTarget($target)) {
            $target = $this->request->get('target');
        }
        if (!$this->isSafeTarget($target)) {
            $target = '/bs/newindex';
        }

        if (
            intval($this->session->get('user_id')) > 0 &&
            intval($this->session->get('company_id')) === intval($company->id) &&
            (string)$this->session->get('login_platform') === 'feishu'
        ) {
            $this->response->redirect($target);
            return;
        }

        $state = bin2hex(openssl_random_pseudo_bytes(16));
        $this->session->set('feishu_oauth_state', $state);
        $this->session->set('feishu_company_id', intval($company->id));
        $this->session->set('feishu_company_hash_key', (string)$company->hash_key);
        $this->session->set('feishu_target', $target);

        $callback = $this->getBaseUrl() . '/feishu/oauthcallback';
        $url = 'https://accounts.feishu.cn/open-apis/authen/v1/authorize?client_id=' .
            rawurlencode($integration->corp_id) .
            '&redirect_uri=' . rawurlencode($callback) .
            '&state=' . rawurlencode($state);
        $this->response->redirect($url, true);
    }

    public function oauthCallbackAction()
    {
        $this->assertFeatureEnabled();
        $state = (string)$this->request->get('state');
        $expected = (string)$this->session->get('feishu_oauth_state');
        if ($state === '' || $expected === '' || !hash_equals($expected, $state)) {
            Utils::showFrontMsg('飞书登录状态校验失败，请重新进入应用');
        }
        $code = trim((string)$this->request->get('code'));
        if ($code === '') {
            Utils::showFrontMsg('飞书未返回授权码，请重新进入应用');
        }

        $companyId = intval($this->session->get('feishu_company_id'));
        $integration = PlatformIntegrationModel::getByCompany($companyId, 'feishu', true);
        if (!$integration) {
            Utils::showFrontMsg('飞书应用未启用');
        }
        $callback = $this->getBaseUrl() . '/feishu/oauthcallback';
        try {
            $client = new FeishuClient($integration);
            $tokenResult = $client->exchangeAuthorizationCode($code, $callback);
            if (empty($tokenResult['access_token'])) {
                throw new \RuntimeException('飞书未返回 user_access_token');
            }
            $userResult = $client->getCurrentUserInfo($tokenResult['access_token']);
            $userInfo = isset($userResult['data']) && is_array($userResult['data'])
                ? $userResult['data'] : array();
        } catch (\Exception $e) {
            Utils::showFrontMsg('飞书登录失败：' . $e->getMessage());
        }

        $externalUserId = !empty($userInfo['open_id']) ? (string)$userInfo['open_id'] : '';
        if ($externalUserId === '') {
            Utils::showFrontMsg('飞书未返回内部成员身份，请确认当前账号属于该企业');
        }
        $identity = PlatformUserIdentityModel::getByExternalId(
            $companyId,
            'feishu',
            $externalUserId
        );
        if (!$identity || intval($identity->status) !== 1) {
            Utils::showFrontMsg('账号尚未同步，请联系管理员同步飞书通讯录');
        }

        $companyHashKey = (string)$this->session->get('feishu_company_hash_key');
        $this->session->set('user_id', intval($identity->company_user_id));
        $this->session->set('company_id', $companyId);
        $this->session->set('company_haskey', $companyHashKey);
        $this->session->set('current_company_haskey', $companyHashKey);
        $this->session->set('login_platform', 'feishu');
        $this->session->remove('feishu_oauth_state');

        $target = $this->session->get('feishu_target');
        if (!$this->isSafeTarget($target)) {
            $target = '/bs/newindex';
        }
        $this->response->redirect($target);
    }

    public function eventAction()
    {
        $this->assertFeatureEnabled();
        if (!$this->request->isPost()) {
            $this->response->setStatusCode(405, 'Method Not Allowed')->send();
            exit;
        }
        $hashKey = $this->getPathValue('/feishu/event/');
        $company = CompanyModel::findFirst("hash_key='" . addslashes($hashKey) . "'");
        if (!$company) {
            $this->response->setStatusCode(404, 'Not Found')->send();
            exit;
        }
        $integration = PlatformIntegrationModel::getByCompany($company->id, 'feishu', true);
        if (!$integration || !$integration->callback_token) {
            $this->response->setStatusCode(403, 'Forbidden')->send();
            exit;
        }

        $rawBody = file_get_contents('php://input');
        try {
            $payload = (new FeishuEventCrypt($integration->encoding_aes_key))->decode(
                $rawBody,
                $this->request->getHeader('X-Lark-Request-Timestamp'),
                $this->request->getHeader('X-Lark-Request-Nonce'),
                $this->request->getHeader('X-Lark-Signature')
            );
        } catch (\Exception $e) {
            $this->response->setStatusCode(403, 'Forbidden')->send();
            exit;
        }

        $token = !empty($payload['token']) ? (string)$payload['token'] : '';
        if ($token === '' && !empty($payload['header']['token'])) {
            $token = (string)$payload['header']['token'];
        }
        if ($token === '' || !hash_equals((string)$integration->callback_token, $token)) {
            $this->response->setStatusCode(403, 'Forbidden')->send();
            exit;
        }

        if (!empty($payload['challenge'])) {
            $this->sendEventResponse(array('challenge' => (string)$payload['challenge']), true);
        }

        $eventType = !empty($payload['header']['event_type'])
            ? (string)$payload['header']['event_type']
            : (!empty($payload['event']['type']) ? (string)$payload['event']['type'] : 'unknown');
        $eventId = !empty($payload['header']['event_id'])
            ? (string)$payload['header']['event_id']
            : (!empty($payload['uuid']) ? (string)$payload['uuid'] : sha1($rawBody));
        $eventKey = sha1(intval($company->id) . '|' . $eventId);
        $log = PlatformEventLogModel::findFirst(
            'company_id=' . intval($company->id) .
            " and platform='feishu' and event_key='" . addslashes($eventKey) . "'"
        );
        if ($log) {
            $this->sendEventResponse(array('code' => 0), true);
        }

        $log = new PlatformEventLogModel();
        $log->company_id = $company->id;
        $log->platform = 'feishu';
        $log->event_key = $eventKey;
        $log->event_type = $eventType;
        $log->status = 'received';
        $log->payload = json_encode($payload, JSON_UNESCAPED_UNICODE);
        $log->received_at = time();
        $log->save();

        $this->sendEventResponse(array('code' => 0), false);
        if ($this->isContactEvent($eventType)) {
            try {
                (new FeishuSyncService($integration))->syncAll();
                $log->status = 'processed';
                $log->save();
            } catch (\Exception $e) {
                $log->status = 'failed';
                $log->save();
                $integration->last_error = $e->getMessage();
                $integration->updated_at = time();
                $integration->save();
            }
        }
        exit;
    }

    private function sendEventResponse($data, $exitNow)
    {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        if ($exitNow) {
            exit;
        }
        if (function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
        } else {
            @ob_flush();
            flush();
        }
    }

    private function isContactEvent($eventType)
    {
        return strpos($eventType, 'contact.user.') === 0 ||
            strpos($eventType, 'contact.department.') === 0;
    }

    private function assertFeatureEnabled()
    {
        $config = FactoryDefault::getDefault()->get('config');
        if (!isset($config->feishu) || (string)$config->feishu->enabled !== 'true') {
            $this->response->setStatusCode(404, 'Not Found')->send();
            exit;
        }
    }

    private function getBaseUrl()
    {
        $config = FactoryDefault::getDefault()->get('config');
        if (!isset($config->feishu) || empty($config->feishu->base_url)) {
            throw new \RuntimeException('飞书访问域名未配置');
        }
        return rtrim((string)$config->feishu->base_url, '/');
    }

    private function parseEntryPath()
    {
        $pathValue = $this->getPathValue('/feishu/entry/');
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
        return is_string($target) && strlen($target) > 0 &&
            $target[0] === '/' && strpos($target, '//') !== 0;
    }
}
