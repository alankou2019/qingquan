<?php
namespace ScshuxCms\Dacang\Helper;

class WecomCrypt
{
    private $token;
    private $aesKey;
    private $corpId;

    public function __construct($token, $encodingAesKey, $corpId)
    {
        $this->token = (string)$token;
        $this->corpId = (string)$corpId;
        $this->aesKey = base64_decode((string)$encodingAesKey . '=');
        if (strlen($this->aesKey) !== 32) {
            throw new \InvalidArgumentException('EncodingAESKey格式错误');
        }
    }

    public function verifySignature($signature, $timestamp, $nonce, $encrypted)
    {
        $items = array($this->token, (string)$timestamp, (string)$nonce, (string)$encrypted);
        sort($items, SORT_STRING);
        return hash_equals(sha1(implode('', $items)), (string)$signature);
    }

    public function decrypt($encrypted)
    {
        $cipherText = base64_decode((string)$encrypted, true);
        if ($cipherText === false) {
            throw new \RuntimeException('企业微信加密消息不是有效Base64');
        }
        $iv = substr($this->aesKey, 0, 16);
        $plain = openssl_decrypt($cipherText, 'AES-256-CBC', $this->aesKey, OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING, $iv);
        if ($plain === false) {
            throw new \RuntimeException('企业微信消息解密失败');
        }
        $plain = $this->pkcs7Unpad($plain);
        if (strlen($plain) < 20) {
            throw new \RuntimeException('企业微信消息长度无效');
        }
        $contentLength = unpack('N', substr($plain, 16, 4));
        $length = intval($contentLength[1]);
        $content = substr($plain, 20, $length);
        $corpId = substr($plain, 20 + $length);
        if ($corpId !== $this->corpId) {
            throw new \RuntimeException('企业微信CorpID校验失败');
        }
        return $content;
    }

    private function pkcs7Unpad($text)
    {
        $length = strlen($text);
        if ($length === 0) {
            return '';
        }
        $pad = ord($text[$length - 1]);
        if ($pad < 1 || $pad > 32) {
            throw new \RuntimeException('企业微信消息填充无效');
        }
        return substr($text, 0, $length - $pad);
    }
}

