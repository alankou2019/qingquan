<?php
namespace ScshuxCms\Dacang\Model;

use ScshuxCms\Core\Model\BaseModel;

class PlatformEventLogModel extends BaseModel
{
    protected static $_instance = null;

    public function getSource()
    {
        return $this->getTableName('platform_event_log');
    }

    public static function factory()
    {
        if (self::$_instance === null) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
}

