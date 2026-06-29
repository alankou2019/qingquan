<?php
namespace ScshuxCms\Dacang\Helper;

use Phalcon\Di\FactoryDefault;

class WecomCredential
{
    private static function getKey()
    {
        $config = FactoryDefault::getDefault()->get('config');
        if (!isset($config->wecom) || empty($config->wecom->credential_key)) {
            throw new \RuntimeException('企业微信凭证加密密钥未配置');
        }
        return hash('sha256', (string)$config->wecom->credential_key, true);
    }

    public static function encrypt($plainText)
    {
        if ($plainText === '' || $plainText === null) {
            return '';
        }
        $iv = openssl_random_pseudo_bytes(16);
        $cipherText = openssl_encrypt((string)$plainText, 'AES-256-CBC', self::getKey(), OPENSSL_RAW_DATA, $iv);
        if ($cipherText === false) {
            throw new \RuntimeException('企业微信凭证加密失败');
        }
        $mac = hash_hmac('sha256', $iv . $cipherText, self::getKey(), true);
        return base64_encode($iv . $mac . $cipherText);
    }

    public static function decrypt($encrypted)
    {
        if ($encrypted === '' || $encrypted === null) {
            return '';
        }
        $raw = base64_decode($encrypted, true);
        if ($raw === false || strlen($raw) < 49) {
            throw new \RuntimeException('企业微信凭证格式无效');
        }
        $iv = substr($raw, 0, 16);
        $mac = substr($raw, 16, 32);
        $cipherText = substr($raw, 48);
        $expected = hash_hmac('sha256', $iv . $cipherText, self::getKey(), true);
        if (!hash_equals($mac, $expected)) {
            throw new \RuntimeException('企业微信凭证校验失败');
        }
        $plainText = openssl_decrypt($cipherText, 'AES-256-CBC', self::getKey(), OPENSSL_RAW_DATA, $iv);
        if ($plainText === false) {
            throw new \RuntimeException('企业微信凭证解密失败');
        }
        return $plainText;
    }
}

