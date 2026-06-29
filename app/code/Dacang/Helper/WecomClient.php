<?php
namespace ScshuxCms\Dacang\Helper;

use ScshuxCms\Core\Helper;
use ScshuxCms\Dacang\Model\PlatformIntegrationModel;

class WecomClient
{
    const API_BASE = 'https://qyapi.weixin.qq.com/cgi-bin';

    private $integration;
    private $lastError = '';

    public function __construct(PlatformIntegrationModel $integration)
    {
        $this->integration = $integration;
    }

    public function getLastError()
    {
        return $this->lastError;
    }

    public function getAccessToken($forceRefresh = false)
    {
        $cache = Helper::factory()->getCache();
        $cacheKey = 'wecom_access_token_' . intval($this->integration->company_id);
        if (!$forceRefresh) {
            $cached = $cache->get($cacheKey);
            if (!empty($cached)) {
                return $cached;
            }
        }

        $secret = WecomCredential::decrypt($this->integration->secret_enc);
        $url = self::API_BASE . '/gettoken?corpid=' . rawurlencode($this->integration->corp_id) .
            '&corpsecret=' . rawurlencode($secret);
        $result = $this->request($url, null, false);
        if (!$result || empty($result['access_token'])) {
            throw new \RuntimeException($this->lastError ?: '无法获取企业微信access_token');
        }
        $lifetime = !empty($result['expires_in']) ? max(60, intval($result['expires_in']) - 300) : 6900;
        $cache->save($cacheKey, $result['access_token'], $lifetime);
        return $result['access_token'];
    }

    public function testConnection()
    {
        $token = $this->getAccessToken(true);
        return $this->apiGet('/agent/get', array('agentid' => $this->integration->agent_id), $token);
    }

    public function getUserByCode($code)
    {
        return $this->apiGet('/auth/getuserinfo', array('code' => $code));
    }

    public function getDepartments()
    {
        $result = $this->apiGet('/department/list');
        return isset($result['department']) ? $result['department'] : array();
    }

    public function getDepartmentUsers($departmentId, $fetchChild = false)
    {
        $result = $this->apiGet('/user/list', array(
            'department_id' => intval($departmentId),
            'fetch_child' => $fetchChild ? 1 : 0,
        ));
        return isset($result['userlist']) ? $result['userlist'] : array();
    }

    public function sendTextCard($externalUserIds, $title, $description, $url)
    {
        $users = is_array($externalUserIds) ? implode('|', $externalUserIds) : $externalUserIds;
        return $this->apiPost('/message/send', array(
            'touser' => $users,
            'msgtype' => 'textcard',
            'agentid' => intval($this->integration->agent_id),
            'textcard' => array(
                'title' => $title,
                'description' => $description,
                'url' => $url,
                'btntxt' => '查看详情',
            ),
            'enable_id_trans' => 0,
        ));
    }

    private function apiGet($path, $params = array(), $token = null)
    {
        if ($token === null) {
            $token = $this->getAccessToken();
        }
        $params = array_merge(array('access_token' => $token), $params);
        return $this->request(self::API_BASE . $path . '?' . http_build_query($params), null, false);
    }

    private function apiPost($path, $payload)
    {
        $url = self::API_BASE . $path . '?access_token=' . rawurlencode($this->getAccessToken());
        return $this->request($url, $payload, true);
    }

    private function request($url, $payload, $isPost)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 8);
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: application/json', 'Content-Type: application/json'));
        if ($isPost) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload, JSON_UNESCAPED_UNICODE));
        }
        $body = curl_exec($ch);
        $httpCode = intval(curl_getinfo($ch, CURLINFO_HTTP_CODE));
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($body === false || $httpCode < 200 || $httpCode >= 300) {
            $this->lastError = $curlError ?: '企业微信接口HTTP错误: ' . $httpCode;
            return false;
        }
        $result = json_decode($body, true);
        if (!is_array($result)) {
            $this->lastError = '企业微信接口返回无法解析';
            return false;
        }
        if (isset($result['errcode']) && intval($result['errcode']) !== 0) {
            $this->lastError = '企业微信错误 ' . $result['errcode'] . ': ' . (isset($result['errmsg']) ? $result['errmsg'] : 'unknown');
            throw new \RuntimeException($this->lastError);
        }
        return $result;
    }
}

