<?php
namespace ScshuxCms\Dacang\Helper;

class MiniappBackendSync
{
	public function sync($company, $modules)
	{
		$url = trim((string)getenv('MINIAPP_SYNC_URL'));
		$token = trim((string)getenv('MINIAPP_SYNC_TOKEN'));
		if ($url === '' || $token === '') {
			return array(
				'success' => false,
				'skipped' => true,
				'error' => '未配置 MINIAPP_SYNC_URL 或 MINIAPP_SYNC_TOKEN'
			);
		}
		if (!function_exists('curl_init')) {
			return array(
				'success' => false,
				'skipped' => false,
				'error' => '服务器未启用 cURL，无法同步小程序企业'
			);
		}

		$backendModules = array();
		foreach ($modules as $module) {
			$backendModule = $module === 'salary' ? 'payroll' : $module;
			if (!in_array($backendModule, $backendModules)) {
				$backendModules[] = $backendModule;
			}
		}
		$payload = array(
			'legacy_company_id' => intval($company->id),
			'name' => $company->name,
			'admin_mobile' => $company->miniapp_admin_mobile,
			'contact_name' => $company->miniapp_admin_name,
			'contact_phone' => $company->miniapp_admin_mobile,
			'employee_limit' => intval($company->personlimit),
			'status' => intval($company->status) > 0 ? 'active' : 'inactive',
			'expires_at' => intval($company->expire_time) > 0 ? date('Y-m-d H:i:s', intval($company->expire_time)) : null,
			'modules' => $backendModules
		);

		$curl = curl_init($url);
		curl_setopt_array($curl, array(
			CURLOPT_POST => true,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_CONNECTTIMEOUT => 3,
			CURLOPT_TIMEOUT => 10,
			CURLOPT_HTTPHEADER => array(
				'Content-Type: application/json',
				'X-Legacy-Sync-Token: ' . $token
			),
			CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE)
		));
		$response = curl_exec($curl);
		$error = curl_error($curl);
		$statusCode = intval(curl_getinfo($curl, CURLINFO_HTTP_CODE));
		curl_close($curl);

		if ($response === false) {
			return array('success' => false, 'skipped' => false, 'error' => $error ?: '同步请求失败');
		}
		$body = json_decode($response, true);
		if ($statusCode < 200 || $statusCode >= 300 || !is_array($body) || !isset($body['code']) || intval($body['code']) !== 0) {
			$message = is_array($body) && !empty($body['message']) ? $body['message'] : '同步接口返回异常';
			return array('success' => false, 'skipped' => false, 'error' => $message);
		}

		return array('success' => true, 'skipped' => false, 'error' => '');
	}
}