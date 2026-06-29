<?php
namespace ScshuxCms\Dacang\Model;

use ScshuxCms\Core\Model\BaseModel;

class PlatformUserIdentityModel extends BaseModel
{
    protected static $_instance = null;

    public function getSource()
    {
        return $this->getTableName('platform_user_identity');
    }

    public static function factory()
    {
        if (self::$_instance === null) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public static function getByExternalId($companyId, $platform, $externalUserId)
    {
        return self::findFirst(
            'company_id=' . intval($companyId) .
            " and platform='" . addslashes($platform) . "'" .
            " and external_user_id='" . addslashes($externalUserId) . "'"
        );
    }
}

