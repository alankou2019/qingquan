<?php
namespace ScshuxCms\Dacang\Helper;

use Phalcon\Di\FactoryDefault;
use ScshuxCms\Dacang\Model\CompanyModel;
use ScshuxCms\Dacang\Model\PlatformIntegrationModel;
use ScshuxCms\Dacang\Model\PlatformUserIdentityModel;

class FeishuNotifier
{
    public static function isConfigured($companyId)
    {
        return (bool)PlatformIntegrationModel::getByCompany($companyId, 'feishu');
    }

    public static function isEnabled($companyId)
    {
        return self::isFeatureEnabled() &&
            (bool)PlatformIntegrationModel::getByCompany($companyId, 'feishu', true);
    }

    public static function sendToUsers($companyId, $companyUserIds, $title, $description, $targetPath)
    {
        if (!self::isFeatureEnabled()) {
            return false;
        }
        $integration = PlatformIntegrationModel::getByCompany($companyId, 'feishu', true);
        if (!$integration) {
            return false;
        }
        $ids = is_array($companyUserIds)
            ? $companyUserIds : explode(',', trim($companyUserIds, ','));
        $externalIds = array();
        foreach ($ids as $id) {
            $identity = PlatformUserIdentityModel::findFirst(
                'company_id=' . intval($companyId) .
                " and platform='feishu' and status=1 and company_user_id=" . intval($id)
            );
            if ($identity) {
                $externalIds[] = (string)$identity->external_user_id;
            }
        }
        $externalIds = array_values(array_unique($externalIds));
        if (!$externalIds) {
            return false;
        }

        $company = CompanyModel::findFirst(intval($companyId));
        $config = FactoryDefault::getDefault()->get('config');
        $baseUrl = isset($config->feishu->base_url)
            ? rtrim((string)$config->feishu->base_url, '/') : '';
        if (!$company || $baseUrl === '') {
            self::saveLastError($integration, '飞书通知访问域名或企业信息未配置');
            return false;
        }
        $entryUrl = $baseUrl . '/feishu/entry/' . $company->hash_key .
            '/t/' . self::encodeTargetPath($targetPath);
        $client = new FeishuClient($integration);
        foreach ($externalIds as $externalId) {
            try {
                $client->sendInteractiveCard(
                    $externalId,
                    $title,
                    $description,
                    $entryUrl
                );
            } catch (\Exception $e) {
                self::saveLastError($integration, $e->getMessage());
            }
        }
        return true;
    }

    public static function sendReportStart(
        $companyId,
        $reportId,
        $title,
        $description,
        $isPoint = false
    ) {
        $modelClass = $isPoint
            ? 'ScshuxCms\\Dacang\\Model\\PointReportItemModel'
            : 'ScshuxCms\\Dacang\\Model\\ReportItemModel';
        $items = $modelClass::find('report_id=' . intval($reportId));
        $userIds = array();
        foreach ($items as $item) {
            $userIds[] = intval($item->report_user_id);
        }
        $target = $isPoint ? '/bspoint/index?type=1' : '/bs/index?type=1';
        return self::sendToUsers(
            $companyId,
            array_values(array_unique($userIds)),
            $title,
            $description,
            $target
        );
    }

    public static function sendReportPublished(
        $companyId,
        $assessedUserId,
        $reportName,
        $isPoint = false
    ) {
        $target = $isPoint ? '/bspoint/index?type=3' : '/bs/index?type=3';
        return self::sendToUsers(
            $companyId,
            array(intval($assessedUserId)),
            '考核结果已发布',
            '您的《' . $reportName . '》已经完成，点击查看结果。',
            $target
        );
    }

    private static function encodeTargetPath($targetPath)
    {
        return rtrim(strtr(base64_encode((string)$targetPath), '+/', '-_'), '=');
    }

    private static function isFeatureEnabled()
    {
        $config = FactoryDefault::getDefault()->get('config');
        return isset($config->feishu) && (string)$config->feishu->enabled === 'true';
    }

    private static function saveLastError($integration, $message)
    {
        $integration->last_error = (string)$message;
        $integration->updated_at = time();
        $integration->save();
    }
}
