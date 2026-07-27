<?php
namespace ScshuxCms\Dacang\Helper;

use Phalcon\Di\FactoryDefault;

class FeishuCredential
{
    private static function getKey()
    {
        $config = FactoryDefault::getDefault()->get('config');
        if (isset($config->feishu) && !empty($config->feishu->credential_key)) {
            return hash('sha256', (string)$config->feishu->credential_key, true);
        }
        throw new \RuntimeException('飞书凭证加密密钥未配置');
    }

    public static function encrypt($plainText)
    {
        if ($plainText === '' || $plainText === null) {
            return '';
        }
        $iv = openssl_random_pseudo_bytes(16);
        $key = self::getKey();
        $cipherText = openssl_encrypt((string)$plainText, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
        if ($cipherText === false) {
            throw new \RuntimeException('飞书凭证加密失败');
        }
        $mac = hash_hmac('sha256', $iv . $cipherText, $key, true);
        return base64_encode($iv . $mac . $cipherText);
    }

    public static function decrypt($encrypted)
    {
        if ($encrypted === '' || $encrypted === null) {
            return '';
        }
        $raw = base64_decode($encrypted, true);
        if ($raw === false || strlen($raw) < 49) {
            throw new \RuntimeException('飞书凭证格式无效');
        }
        $iv = substr($raw, 0, 16);
        $mac = substr($raw, 16, 32);
        $cipherText = substr($raw, 48);
        $key = self::getKey();
        $expected = hash_hmac('sha256', $iv . $cipherText, $key, true);
        if (!hash_equals($mac, $expected)) {
            throw new \RuntimeException('飞书凭证校验失败');
        }
        $plainText = openssl_decrypt($cipherText, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
        if ($plainText === false) {
            throw new \RuntimeException('飞书凭证解密失败');
        }
        return $plainText;
    }
}
