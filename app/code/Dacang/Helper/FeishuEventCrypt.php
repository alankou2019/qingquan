<?php
namespace ScshuxCms\Dacang\Helper;

class FeishuEventCrypt
{
    private $encryptKey;

    public function __construct($encryptKey)
    {
        $this->encryptKey = (string)$encryptKey;
    }

    public function decode($rawBody, $timestamp = '', $nonce = '', $signature = '')
    {
        $rawBody = (string)$rawBody;
        if ($rawBody === '') {
            throw new \RuntimeException('飞书回调内容为空');
        }

        $outer = json_decode($rawBody, true);
        if (!is_array($outer)) {
            throw new \RuntimeException('飞书回调 JSON 无法解析');
        }

        if ($this->encryptKey === '') {
            return $outer;
        }
        if (!$this->verifySignature($timestamp, $nonce, $rawBody, $signature)) {
            throw new \RuntimeException('飞书回调签名校验失败');
        }
        if (empty($outer['encrypt'])) {
            throw new \RuntimeException('飞书加密回调缺少 encrypt 字段');
        }

        $encrypted = base64_decode($outer['encrypt'], true);
        if ($encrypted === false || strlen($encrypted) <= 16) {
            throw new \RuntimeException('飞书回调密文格式无效');
        }
        $iv = substr($encrypted, 0, 16);
        $cipherText = substr($encrypted, 16);
        $plainText = openssl_decrypt(
            $cipherText,
            'AES-256-CBC',
            hash('sha256', $this->encryptKey, true),
            OPENSSL_RAW_DATA,
            $iv
        );
        if ($plainText === false) {
            throw new \RuntimeException('飞书回调解密失败');
        }
        $payload = json_decode($plainText, true);
        if (!is_array($payload)) {
            throw new \RuntimeException('飞书回调明文 JSON 无法解析');
        }
        return $payload;
    }

    public function verifySignature($timestamp, $nonce, $rawBody, $signature)
    {
        if ($this->encryptKey === '' || $signature === '') {
            return false;
        }
        $expected = hash(
            'sha256',
            (string)$timestamp . (string)$nonce . $this->encryptKey . (string)$rawBody
        );
        return hash_equals($expected, strtolower((string)$signature));
    }
}
