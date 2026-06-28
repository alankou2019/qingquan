<?php
/**
 * 用户表
 * @copyright Copyright (c) 2011 - 2025 scshux (http://www.scshux.com)
 * @author kaiping.jiang <kaiping.jiang@scshux.com>
 */
namespace  ScshuxCms\User\Model;
use ScshuxCms\Core\Model\BaseModel;
use ScshuxCms\Core\Helper\Utils;
use ScshuxCms\Core\Helper;
use ScshuxCms\Dacang\Model\CompanyModel;
class  UserModel extends BaseModel
{

	protected static  $_instance=null;

	public function getSource()
	{
		return $this->getTableName("user");
	}

	/**
	 * 通过用户名获取用户
	 * @param  $userName
	 * @return \ScshuxCms\User\Model\UserModel
	 */
	public  function  loadUserByUserName($userName)
	{
		$userName = addslashes($userName);
		return $this->findFirst("user_name='{$userName}'");
	}

	/**
	 * 通过phone获取用户
	 * @param  $phone
	 * @return \ScshuxCms\User\Model\UserModel
	 */
	public  function  loadUserByPhone($phone)
	{
		$phone = addslashes($phone);
		return $this->findFirst("phone='{$phone}'");
	}


	/**
	 * 记录用户登录信息
	 */
	public  function addLoginLog()
	{
		$this->login_num = $this->login_num +1;
		$this->last_ip = Utils::getIP();
		$this->last_time = Helper::factory()->getTime()->gmtime();
		$this->save();
	}
	
	
	public  static  function  isLogin()
	{
		$user = Helper::factory()->getSession()->get('_user');
		if(empty($user) || empty($user->user_id))
		{
			return false;
		}
		
		return true;
	}
	
	/**
	 * @desc	获取当前登录的用户
	 * @param			
	 * @return			
	 */
	public function  getUser()
	{
		$user = Helper::factory()->getSession()->get('_user');
		return $user;
	}
	
	/**
	 * @desc	获取当前登录用户的id
	 * @param			
	 * @return			
	 */
	public function getUserId()
	{
		$user = Helper::factory()->getSession()->get('_user');
		return intval($user->user_id);
	}
	
	/**
	 * 登录后执行的动作
	 * @param \ScshuxCms\Common\Model\AdminUserModel $adminUser
	 */
	public  function loginAfter($user,$company)
	{
		if(empty($user)) return false;
			
	
		//保存登录信息
		$data = array(
				'user_id' => $user->user_id,
				'user_name' => $user->user_name,
				'true_name' => $user->true_name,
				'is_admin' => $user->is_admin,
				'company_id' => $user->company_id,
				'company_name' => $company->name,
				'phone'=> $user->phone,
				'login_num' => $user->login_num
		);
		$data =  arrayToObject($data);
		Helper::factory()->getSession()->set('_user', $data);
	
		//保存日志
		//AdminLogModel::factory()->addLog('登录系统!',$User->user_id);//登录日志
		$user->save(array(
				'last_ip' => Utils::getIP(),
				'last_time' => Helper::factory()->getTime()->gmtime(),
				'login_num'  => $user->login_num + 1
		));
	
	}

	/**
	 * 返回实例
	 * @return \ScshuxCms\User\Model\UserModel
	 */
	public static  function factory()
	{
		if(self::$_instance==null)
		{
			self::$_instance = new UserModel();
		}
		return self::$_instance;
	}

	
	
	/**
	 * 
	 * @desc 通过id  获取用户名的字符串
	 * @param $ids
	 * @param $companyId
	 * @date 2017年4月21日
	 */
	public static function getUserByids($ids,$companyId)
	{
		$return  = '';
		if(!$ids || !$companyId)
		{
			return $return ;
		}
		
		$ids = trim($ids) ;
		$companyId = intval($companyId) ;
		
		
		$items = self::find("company_id = {$companyId} and user_id in ({$ids})") ;
		if($items)
		{
			foreach ($items as $item)
			{
				$return .= $item->user_name.',';
			}
			$return = trim($return,',') ;
		}
		
		return $return ;
	}
	
	
	/**
	 * 
	 * @desc	自动生成用户
	 * @param	array  $data  参数数组
	 * @return	bool   是否成功
	 * @date	2017年5月2日
	 */
	public static function createUser($data)
	{
		$return = false ;
		if (!$data || !is_array($data))
		{
			return $return ;
		}
		
		$userName = empty($data['phone'])?'dacang'.$data['company_id']:$data['phone'];
		$adddata = array(
				'user_name'  => $data['phone'] ,
				'company_id' => $data['company_id'],
				'phone'      => $data['phone'] ,
				'password'   => md5('dacang2017'),
				'created'    => Helper::factory()->getTime()->gmtime(),
				'reg_ip'     => Helper::factory()->getIp(),
				'is_admin'   => 1
		);
		
		return UserModel::factory()->save($adddata) ;
	}
	
	
	
	/**
	 * @desc	返回管理员表示
	 * @param			
	 * @return			
	 */
	public static function getAdminName($is_admin)
	{
		$return=array(
				'1'=>'管理员',
				'0'=>'非管理员'
		);
		
		if ($is_admin && in_array($is_admin, array(0,1)))
		{
			return $return[$is_admin];
		}
		
		return $return;
		
	}
	
	
	/**
	 * @desc	获取当前用户所属公司id
	 * @param			
	 * @return			
	 */
	public static function getCompanyId()
	{
		$user=self::factory()->getUser();
		return $user->company_id;
	}
	
	/**
	 * @desc	检查是否已经开通了 积分考评模块
	 * @param			
	 * @return	bool		
	 */
	public static function checkPointModule()
	{
		$companyId=self::getCompanyId();
		$companyInfo=CompanyModel::findFirst($companyId);
		if ($companyInfo && $companyInfo->pointstatus)
		{
			return true;
		}
		return false;
	}

}