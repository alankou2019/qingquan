<?php
namespace ScshuxCms\Adminhtml\Controller;

use ScshuxCms\Core\Controller\AdminBaseController;
use ScshuxCms\Core\Helper\Utils;
use ScshuxCms\Dacang\Helper\WecomClient;
use ScshuxCms\Dacang\Helper\WecomCredential;
use ScshuxCms\Dacang\Helper\WecomSyncService;
use ScshuxCms\Dacang\Model\CompanyModel;
use ScshuxCms\Dacang\Model\PlatformIntegrationModel;
use Phalcon\Di\FactoryDefault;

class WecomController extends AdminBaseController
{
    public function indexAction()
    {
        $companyId = intval($this->request->get('company_id'));
        $companies = CompanyModel::find(array('order' => 'id desc'));
        $companyHashKey = '';
        foreach ($companies as $companyItem) {
            if (intval($companyItem->id) === $companyId) {
                $companyHashKey = (string)$companyItem->hash_key;
                break;
            }
        }
        $integration = $companyId ? PlatformIntegrationModel::getByCompany($companyId) : null;
        $config = FactoryDefault::getDefault()->get('config');
        $baseUrl = isset($config->wecom->base_url) ? rtrim((string)$config->wecom->base_url, '/') : '';
        $this->view->setVar('companies', $companies);
        $this->view->setVar('company_id', $companyId);
        $this->view->setVar('company_hash_key', $companyHashKey);
        $this->view->setVar('integration', $integration);
        $this->view->setVar('wecom_base_url', $baseUrl);
    }

    public function saveAction()
    {
        $backUrl = $this->getHelper()->createUrl(array('p' => 'wecom/index', 'company_id' => intval($this->request->get('company_id'))));
        if (!$this->request->isPost()) {
            Utils::showMsg('不支持的请求方式', $backUrl);
        }
        $companyId = intval($this->request->get('company_id'));
        $company = null;
        foreach (CompanyModel::find() as $companyItem) {
            if (intval($companyItem->id) === $companyId) {
                $company = $companyItem;
                break;
            }
        }
        if (!$company) {
            Utils::showMsg('公司不存在', $backUrl);
        }
        $corpId = trim($this->request->get('corp_id'));
        $agentId = trim($this->request->get('agent_id'));
        $secret = trim($this->request->get('secret'));
        if ($corpId === '' || $agentId === '') {
            Utils::showMsg('请填写CorpID和AgentID', $backUrl);
        }

        $integration = PlatformIntegrationModel::getByCompany($companyId);
        if (!$integration) {
            if ($secret === '') {
                Utils::showMsg('首次配置必须填写Secret', $backUrl);
            }
            $integration = new PlatformIntegrationModel();
            $integration->company_id = $companyId;
            $integration->platform = 'wecom';
            $integration->created_at = time();
        }
        $integration->corp_id = $corpId;
        $integration->agent_id = $agentId;
        if ($secret !== '') {
            $integration->secret_enc = WecomCredential::encrypt($secret);
        }
        $integration->callback_token = trim($this->request->get('callback_token'));
        $integration->encoding_aes_key = trim($this->request->get('encoding_aes_key'));
        $integration->enabled = intval($this->request->get('enabled')) === 1 ? 1 : 0;
        $integration->updated_at = time();
        if (!$integration->save()) {
            Utils::showMsg('保存失败', $backUrl);
        }
        Utils::showMsg('企业微信配置已保存', $backUrl);
    }

    public function testAction()
    {
        $integration = $this->getIntegration();
        try {
            $result = (new WecomClient($integration))->testConnection();
            $this->sendSuccessResult(array('name' => isset($result['name']) ? $result['name'] : '连接成功'));
        } catch (\Exception $e) {
            $integration->last_error = $e->getMessage();
            $integration->updated_at = time();
            $integration->save();
            $this->sendErrorResult($e->getMessage());
        }
    }

    public function syncAction()
    {
        $integration = $this->getIntegration();
        try {
            $result = (new WecomSyncService($integration))->syncAll();
            $this->sendSuccessResult($result);
        } catch (\Exception $e) {
            $integration->last_error = $e->getMessage();
            $integration->updated_at = time();
            $integration->save();
            $this->sendErrorResult($e->getMessage());
        }
    }

    private function getIntegration()
    {
        if (!$this->request->isPost() || !$this->request->isAjax()) {
            $this->sendErrorResult('请求方式错误');
        }
        $companyId = intval($this->request->get('company_id'));
        $integration = PlatformIntegrationModel::getByCompany($companyId, 'wecom', true);
        if (!$integration) {
            $this->sendErrorResult('企业微信配置不存在或尚未启用');
        }
        return $integration;
    }
}
