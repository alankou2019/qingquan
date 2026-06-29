<?php
namespace ScshuxCms\Dacang\Helper;

use Phalcon\Di\FactoryDefault;
use ScshuxCms\Dacang\Model\CompanyModel;
use ScshuxCms\Dacang\Model\PlatformIntegrationModel;
use ScshuxCms\Dacang\Model\PlatformUserIdentityModel;
use ScshuxCms\Dacang\Model\ReportItemModel;
use ScshuxCms\Dacang\Model\PointReportItemModel;

class WecomNotifier
{
    public static function isEnabled($companyId)
    {
        return (bool)PlatformIntegrationModel::getByCompany($companyId, 'wecom', true);
    }

    public static function sendToUsers($companyId, $companyUserIds, $title, $description, $targetPath)
    {
        $integration = PlatformIntegrationModel::getByCompany($companyId, 'wecom', true);
        if (!$integration) {
            return false;
        }
        $ids = is_array($companyUserIds) ? $companyUserIds : explode(',', trim($companyUserIds, ','));
        $externalIds = array();
        foreach ($ids as $id) {
            $identity = PlatformUserIdentityModel::findFirst(
                'company_id=' . intval($companyId) . " and platform='wecom' and status=1 and company_user_id=" . intval($id)
            );
            if ($identity) {
                $externalIds[] = $identity->external_user_id;
            }
        }
        $externalIds = array_values(array_unique($externalIds));
        if (!$externalIds) {
            return false;
        }

        $company = CompanyModel::findFirst(intval($companyId));
        $config = FactoryDefault::getDefault()->get('config');
        $baseUrl = isset($config->wecom->base_url) ? rtrim((string)$config->wecom->base_url, '/') : '';
        $entryUrl = $baseUrl . '/wecom/entry/' . $company->hash_key . '/t/' . self::encodeTargetPath($targetPath);
        $client = new WecomClient($integration);
        foreach (array_chunk($externalIds, 1000) as $chunk) {
            $client->sendTextCard($chunk, $title, $description, $entryUrl);
        }
        return true;
    }

    private static function encodeTargetPath($targetPath)
    {
        return rtrim(strtr(base64_encode((string)$targetPath), '+/', '-_'), '=');
    }

    public static function sendReportStart($companyId, $reportId, $title, $description, $isPoint = false)
    {
        $modelClass = $isPoint ? 'ScshuxCms\\Dacang\\Model\\PointReportItemModel' : 'ScshuxCms\\Dacang\\Model\\ReportItemModel';
        $items = $modelClass::find('report_id=' . intval($reportId));
        $userIds = array();
        foreach ($items as $item) {
            $userIds[] = intval($item->report_user_id);
        }
        $target = $isPoint ? '/bspoint/index?type=1' : '/bs/index?type=1';
        return self::sendToUsers($companyId, array_values(array_unique($userIds)), $title, $description, $target);
    }

    public static function sendReportPublished($companyId, $assessedUserId, $reportName, $isPoint = false)
    {
        $target = $isPoint ? '/bspoint/index?type=3' : '/bs/index?type=3';
        return self::sendToUsers(
            $companyId,
            array(intval($assessedUserId)),
            '考核结果已发布',
            '您的《' . $reportName . '》已经完成，点击查看结果。',
            $target
        );
    }
}
