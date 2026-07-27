<?php
namespace ScshuxCms\Adminhtml\Controller;

use Phalcon\Di\FactoryDefault;
use ScshuxCms\Core\Controller\AdminBaseController;
use ScshuxCms\Core\Helper\Utils;
use ScshuxCms\Dacang\Helper\FeishuClient;
use ScshuxCms\Dacang\Helper\FeishuCredential;
use ScshuxCms\Dacang\Helper\FeishuSyncService;
use ScshuxCms\Dacang\Model\CompanyModel;
use ScshuxCms\Dacang\Model\PlatformIntegrationModel;

class FeishuController extends AdminBaseController
{
    public function indexAction()
    {
        $companyId = intval($this->request->get('company_id'));
        $companies = $this->getFeishuCompanies();
        $company = $companyId ? CompanyModel::findFirst($companyId) : null;
        $integration = $companyId
            ? PlatformIntegrationModel::getByCompany($companyId, 'feishu')
            : null;

        $config = FactoryDefault::getDefault()->get('config');
        $baseUrl = '';
        if (isset($config->feishu) && !empty($config->feishu->base_url)) {
            $baseUrl = rtrim((string)$config->feishu->base_url, '/');
        } elseif (!empty($_SERVER['HTTP_HOST'])) {
            $scheme = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http';
            $baseUrl = $scheme . '://' . $_SERVER['HTTP_HOST'];
        }

        $this->view->setVar('companies', $companies);
        $this->view->setVar('company_id', $companyId);
        $this->view->setVar('company', $company);
        $this->view->setVar('company_hash_key', $company ? (string)$company->hash_key : '');
        $this->view->setVar('integration', $integration);
        $this->view->setVar('feishu_base_url', $baseUrl);
        $this->view->setVar('feishu_feature_enabled', $this->isFeatureEnabled());
    }

    public function saveAction()
    {
        $companyId = intval($this->request->get('company_id'));
        $backUrl = $this->getHelper()->createUrl(array(
            'p' => 'feishu/index',
            'company_id' => $companyId,
        ));
        if (!$this->request->isPost()) {
            Utils::showMsg('不支持的请求方式', $backUrl);
        }
        $company = CompanyModel::findFirst($companyId);
        if (!$company) {
            Utils::showMsg('公司不存在', $backUrl);
        }

        $appId = trim($this->request->get('app_id'));
        $appSecret = trim($this->request->get('app_secret'));
        if ($appId === '') {
            Utils::showMsg('请填写 App ID', $backUrl);
        }

        $integration = PlatformIntegrationModel::getByCompany($companyId, 'feishu');
        if (!$integration) {
            if ($appSecret === '') {
                Utils::showMsg('首次配置必须填写 App Secret', $backUrl);
            }
            $integration = new PlatformIntegrationModel();
            $integration->company_id = $companyId;
            $integration->platform = 'feishu';
            $integration->agent_id = '';
            $integration->created_at = time();
        }
        $integration->corp_id = $appId;
        if ($appSecret !== '') {
            $integration->secret_enc = FeishuCredential::encrypt($appSecret);
        }
        $integration->callback_token = trim($this->request->get('verification_token'));
        $integration->encoding_aes_key = trim($this->request->get('encrypt_key'));
        $enabled = intval($this->request->get('enabled')) === 1 ? 1 : 0;
        if ($enabled && !$this->isFeatureEnabled()) {
            Utils::showMsg('飞书系统总开关尚未打开，请先保存为关闭状态并完成连接测试', $backUrl);
        }
        $integration->enabled = $enabled;
        $integration->updated_at = time();
        if (!$integration->save()) {
            Utils::showMsg('飞书配置保存失败', $backUrl);
        }
        Utils::showMsg('飞书配置已保存', $backUrl);
    }

    public function testAction()
    {
        $integration = $this->getIntegration(false);
        try {
            $result = (new FeishuClient($integration))->testConnection();
            $integration->last_error = '';
            $integration->updated_at = time();
            $integration->save();
            $this->sendSuccessResult($result);
        } catch (\Exception $e) {
            $integration->last_error = $e->getMessage();
            $integration->updated_at = time();
            $integration->save();
            $this->sendErrorResult($e->getMessage());
        }
    }

    public function syncAction()
    {
        if (!$this->isFeatureEnabled()) {
            $this->sendErrorResult('飞书系统总开关尚未打开');
        }
        $integration = $this->getIntegration(true);
        try {
            $result = (new FeishuSyncService($integration))->syncAll();
            $this->sendSuccessResult($result);
        } catch (\Exception $e) {
            $integration->last_error = $e->getMessage();
            $integration->updated_at = time();
            $integration->save();
            $this->sendErrorResult($e->getMessage());
        }
    }

    private function getFeishuCompanies()
    {
        $companies = array();
        $seen = array();
        foreach (PlatformIntegrationModel::find("platform='feishu'") as $integration) {
            $companyId = intval($integration->company_id);
            if (isset($seen[$companyId])) {
                continue;
            }
            $company = CompanyModel::findFirst($companyId);
            if ($company) {
                $companies[] = $company;
                $seen[$companyId] = true;
            }
        }
        return $companies;
    }

    private function getIntegration($enabledOnly)
    {
        if (!$this->request->isPost() || !$this->request->isAjax()) {
            $this->sendErrorResult('请求方式错误');
        }
        $companyId = intval($this->request->get('company_id'));
        $integration = PlatformIntegrationModel::getByCompany(
            $companyId,
            'feishu',
            (bool)$enabledOnly
        );
        if (!$integration) {
            $message = $enabledOnly ? '飞书配置不存在或尚未启用' : '飞书配置不存在';
            $this->sendErrorResult($message);
        }
        return $integration;
    }

    private function isFeatureEnabled()
    {
        $config = FactoryDefault::getDefault()->get('config');
        return isset($config->feishu) && (string)$config->feishu->enabled === 'true';
    }
}
