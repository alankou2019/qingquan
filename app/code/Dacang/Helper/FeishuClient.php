<?php
namespace ScshuxCms\Dacang\Helper;

use ScshuxCms\Core\Helper;
use ScshuxCms\Dacang\Model\PlatformIntegrationModel;

class FeishuClient
{
    const API_BASE = 'https://open.feishu.cn/open-apis';

    private $integration;
    private $tenantAccessToken = '';
    private $lastError = '';

    public function __construct(PlatformIntegrationModel $integration)
    {
        $this->integration = $integration;
    }

    public function getLastError()
    {
        return $this->lastError;
    }

    public function getTenantAccessToken($forceRefresh = false)
    {
        if (!$forceRefresh && $this->tenantAccessToken !== '') {
            return $this->tenantAccessToken;
        }
        $cache = Helper::factory()->getCache();
        $cacheKey = 'feishu_tenant_access_token_' . intval($this->integration->company_id);
        if (!$forceRefresh) {
            $cached = $cache->get($cacheKey);
            if (!empty($cached)) {
                $this->tenantAccessToken = (string)$cached;
                return $this->tenantAccessToken;
            }
        }
        $result = $this->request(
            self::API_BASE . '/auth/v3/tenant_access_token/internal',
            array(
                'app_id' => (string)$this->integration->corp_id,
                'app_secret' => FeishuCredential::decrypt($this->integration->secret_enc),
            ),
            'POST',
            ''
        );
        if (empty($result['tenant_access_token'])) {
            throw new \RuntimeException('无法获取飞书 tenant_access_token');
        }
        $this->tenantAccessToken = (string)$result['tenant_access_token'];
        $lifetime = !empty($result['expire'])
            ? max(60, intval($result['expire']) - 300) : 6900;
        $cache->save($cacheKey, $this->tenantAccessToken, $lifetime);
        return $this->tenantAccessToken;
    }

    public function testConnection()
    {
        $this->getTenantAccessToken(true);
        $this->apiGet('/contact/v3/departments/0/children', array(
            'department_id_type' => 'open_department_id',
            'page_size' => 1,
        ));
        return array('app_id' => (string)$this->integration->corp_id, 'connected' => true);
    }

    public function getAllDepartments()
    {
        $result = array();
        $queue = array('0');
        $visited = array();
        while ($queue) {
            $parentId = array_shift($queue);
            if (isset($visited[$parentId])) {
                continue;
            }
            $visited[$parentId] = true;
            $pageToken = '';
            do {
                $params = array(
                    'department_id_type' => 'open_department_id',
                    'page_size' => 50,
                );
                if ($pageToken !== '') {
                    $params['page_token'] = $pageToken;
                }
                $response = $this->apiGet(
                    '/contact/v3/departments/' . rawurlencode($parentId) . '/children',
                    $params
                );
                $data = isset($response['data']) && is_array($response['data']) ? $response['data'] : array();
                $items = isset($data['items']) && is_array($data['items']) ? $data['items'] : array();
                foreach ($items as $item) {
                    $externalId = $this->getDepartmentExternalId($item);
                    if ($externalId === '') {
                        continue;
                    }
                    if (empty($item['parent_department_id'])) {
                        $item['parent_department_id'] = $parentId;
                    }
                    $result[$externalId] = $item;
                    $queue[] = $externalId;
                }
                $pageToken = !empty($data['has_more']) && !empty($data['page_token'])
                    ? (string)$data['page_token'] : '';
            } while ($pageToken !== '');
        }
        return array_values($result);
    }

    public function getDepartmentUsers($departmentId)
    {
        $users = array();
        $pageToken = '';
        do {
            $params = array(
                'department_id' => (string)$departmentId,
                'department_id_type' => 'open_department_id',
                'user_id_type' => 'open_id',
                'page_size' => 50,
            );
            if ($pageToken !== '') {
                $params['page_token'] = $pageToken;
            }
            $response = $this->apiGet('/contact/v3/users/find_by_department', $params);
            $data = isset($response['data']) && is_array($response['data']) ? $response['data'] : array();
            $items = isset($data['items']) && is_array($data['items']) ? $data['items'] : array();
            foreach ($items as $item) {
                $users[] = $item;
            }
            $pageToken = !empty($data['has_more']) && !empty($data['page_token'])
                ? (string)$data['page_token'] : '';
        } while ($pageToken !== '');
        return $users;
    }

    public function exchangeAuthorizationCode($code, $redirectUri)
    {
        return $this->request(
            self::API_BASE . '/authen/v2/oauth/token',
            array(
                'grant_type' => 'authorization_code',
                'client_id' => (string)$this->integration->corp_id,
                'client_secret' => FeishuCredential::decrypt($this->integration->secret_enc),
                'code' => (string)$code,
                'redirect_uri' => (string)$redirectUri,
            ),
            'POST',
            ''
        );
    }

    public function getCurrentUserInfo($userAccessToken)
    {
        return $this->request(
            self::API_BASE . '/authen/v1/user_info',
            null,
            'GET',
            (string)$userAccessToken
        );
    }

    public function sendInteractiveCard($externalUserId, $title, $description, $url)
    {
        $card = array(
            'config' => array('wide_screen_mode' => true),
            'header' => array(
                'template' => 'blue',
                'title' => array('tag' => 'plain_text', 'content' => (string)$title),
            ),
            'elements' => array(
                array(
                    'tag' => 'div',
                    'text' => array('tag' => 'lark_md', 'content' => (string)$description),
                ),
                array(
                    'tag' => 'action',
                    'actions' => array(
                        array(
                            'tag' => 'button',
                            'type' => 'primary',
                            'text' => array('tag' => 'plain_text', 'content' => '查看详情'),
                            'url' => (string)$url,
                        ),
                    ),
                ),
            ),
        );
        return $this->request(
            self::API_BASE . '/im/v1/messages?receive_id_type=open_id',
            array(
                'receive_id' => (string)$externalUserId,
                'msg_type' => 'interactive',
                'content' => json_encode($card, JSON_UNESCAPED_UNICODE),
            ),
            'POST',
            $this->getTenantAccessToken()
        );
    }

    private function getDepartmentExternalId($department)
    {
        if (!empty($department['open_department_id'])) {
            return (string)$department['open_department_id'];
        }
        return !empty($department['department_id']) ? (string)$department['department_id'] : '';
    }

    private function apiGet($path, $params = array())
    {
        $url = self::API_BASE . $path;
        if ($params) {
            $url .= '?' . http_build_query($params);
        }
        return $this->request($url, null, 'GET', $this->getTenantAccessToken());
    }

    private function request($url, $payload, $method, $accessToken)
    {
        $headers = array('Accept: application/json', 'Content-Type: application/json; charset=utf-8');
        if ($accessToken !== '') {
            $headers[] = 'Authorization: Bearer ' . $accessToken;
        }
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 8);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload, JSON_UNESCAPED_UNICODE));
        }
        $body = curl_exec($ch);
        $httpCode = intval(curl_getinfo($ch, CURLINFO_HTTP_CODE));
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($body === false || $httpCode < 200 || $httpCode >= 300) {
            $this->lastError = $curlError ?: '飞书接口 HTTP 错误: ' . $httpCode;
            throw new \RuntimeException($this->lastError);
        }
        $result = json_decode($body, true);
        if (!is_array($result)) {
            $this->lastError = '飞书接口返回无法解析';
            throw new \RuntimeException($this->lastError);
        }
        if (isset($result['code']) && intval($result['code']) !== 0) {
            $this->lastError = '飞书错误 ' . $result['code'] . ': ' .
                (isset($result['msg']) ? $result['msg'] : 'unknown');
            throw new \RuntimeException($this->lastError);
        }
        return $result;
    }
}
