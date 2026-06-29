<?php
namespace ScshuxCms\Dacang\Model;

use ScshuxCms\Core\Model\BaseModel;

class PlatformIntegrationModel extends BaseModel
{
    protected static $_instance = null;

    public function getSource()
    {
        return $this->getTableName('platform_integration');
    }

    public static function factory()
    {
        if (self::$_instance === null) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public static function getByCompany($companyId, $platform = 'wecom', $enabledOnly = false)
    {
        $where = 'company_id=' . intval($companyId) . " and platform='" . addslashes($platform) . "'";
        if ($enabledOnly) {
            $where .= ' and enabled=1';
        }
        return self::findFirst($where);
    }
}

