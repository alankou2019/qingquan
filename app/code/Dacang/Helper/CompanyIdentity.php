<?php
namespace ScshuxCms\Dacang\Helper;

use ScshuxCms\Dacang\Model\CompanyModel;

class CompanyIdentity
{
	public static function generateCompanyCode($companyName, $exceptCompanyId = 0)
	{
		$base = self::initials($companyName);
		if ($base === '') {
			$base = 'QY';
		}
		$base = substr($base, 0, 8);
		$code = $base;
		$suffix = 1;
		while (self::companyCodeExists($code, $exceptCompanyId)) {
			$suffix++;
			$code = $base . $suffix;
		}
		return $code;
	}

	public static function adminEmployeeNo($companyCode)
	{
		$companyCode = strtoupper(trim($companyCode));
		return ($companyCode !== '' ? $companyCode : 'QY') . '001';
	}

	protected static function companyCodeExists($code, $exceptCompanyId)
	{
		$where = 'company_code="' . addslashes($code) . '"';
		if (intval($exceptCompanyId) > 0) {
			$where .= ' and id!=' . intval($exceptCompanyId);
		}
		return CompanyModel::factory()->findFirst($where) ? true : false;
	}

	protected static function initials($name)
	{
		$name = trim($name);
		$result = '';
		$length = function_exists('mb_strlen') ? mb_strlen($name, 'UTF-8') : strlen($name);
		for ($i = 0; $i < $length; $i++) {
			$char = function_exists('mb_substr') ? mb_substr($name, $i, 1, 'UTF-8') : substr($name, $i, 1);
			if (preg_match('/[A-Za-z]/', $char)) {
				$result .= strtoupper($char);
				continue;
			}
			if (preg_match('/[0-9]/', $char)) {
				$result .= $char;
				continue;
			}
			$result .= self::chineseInitial($char);
		}
		return preg_replace('/[^A-Z0-9]/', '', $result);
	}

	protected static function chineseInitial($char)
	{
		$gbk = @iconv('UTF-8', 'GBK', $char);
		if (!$gbk || strlen($gbk) < 2) {
			return '';
		}
		$code = ord($gbk[0]) * 256 + ord($gbk[1]) - 65536;
		$ranges = array(
			'A' => array(-20319, -20284), 'B' => array(-20283, -19776), 'C' => array(-19775, -19219),
			'D' => array(-19218, -18711), 'E' => array(-18710, -18527), 'F' => array(-18526, -18240),
			'G' => array(-18239, -17923), 'H' => array(-17922, -17418), 'J' => array(-17417, -16475),
			'K' => array(-16474, -16213), 'L' => array(-16212, -15641), 'M' => array(-15640, -15166),
			'N' => array(-15165, -14923), 'O' => array(-14922, -14915), 'P' => array(-14914, -14631),
			'Q' => array(-14630, -14150), 'R' => array(-14149, -14091), 'S' => array(-14090, -13319),
			'T' => array(-13318, -12839), 'W' => array(-12838, -12557), 'X' => array(-12556, -11848),
			'Y' => array(-11847, -11056), 'Z' => array(-11055, -10247)
		);
		foreach ($ranges as $letter => $range) {
			if ($code >= $range[0] && $code <= $range[1]) {
				return $letter;
			}
		}
		return '';
	}
}