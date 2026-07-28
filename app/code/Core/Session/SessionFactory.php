<?php

namespace ScshuxCms\Core\Session;

class SessionFactory
{
    public static function create($config)
    {
        $sessionPath = WEBROOT . '/var/session/';
        if (!file_exists($sessionPath)) {
            mkdir($sessionPath, 0777, true);
        }

        $options = array();
        foreach ($config->session->options as $key => $value) {
            if ($key !== 'name') {
                $options[$key] = $value;
            }
        }

        if (isset($config->session->options->name)) {
            session_name($config->session->options->name);
        }

        $session = new \Phalcon\Session\Manager($options);
        $adapter = new \Phalcon\Session\Adapter\Stream(array(
            'savePath' => $sessionPath,
        ));
        $session->setAdapter($adapter);
        $session->start();

        return $session;
    }
}
