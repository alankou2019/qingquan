<?php
/**
 * 公司管理
 * @copyright Copyright (c) 2011 - 2025 scshux (http://www.scshux.com)
 * @author kaiping.jiang <kaiping.jiang@scshux.com>
 */
namespace ScshuxCms\Adminhtml\Controller;
use ScshuxCms\Core\Controller\AdminBaseController;
use ScshuxCms\Dacang\Model\CompanyModel;
use ScshuxCms\Common\Model\AdminUserModel;
use ScshuxCms\Common\Model\AdminLogModel;
use ScshuxCms\Core\Helper\Utils;
use ScshuxCms\User\Model\UserModel;
use Phalcon\Di\FactoryDefault;
use ScshuxCms\Dacang\Model\CompanyUserModel;
use ScshuxCms\Salary\Model\CompanyModuleAuthModel;
class  CompanyController extends AdminBaseController
{
    /**
     *
    |+----------------------------------------
    | 公司首页
    |+----------------------------------------
     */
	public function indexAction()
	{
	    $act = isset($_REQUEST['act'])?$_REQUEST['act']:'';
	    $isAjax = isset($_REQUEST['is_ajax'])?$_REQUEST['is_ajax']:false;
	    
	    if($act == 'remove'){
	        $this->_remove($_REQUEST['id']);
	    }
	    $dataList = $this->_getDataList();
	    $this->view->setVar('dataList', $dataList);
	    $this->view->setVar('full_page',1);
	    if($isAjax)
	    {
	        $this->view->setMainView(false);
	        $this->view->start();
	        $this->view->setVar('full_page',0);
	        $this->view->render('company','index');
	        $this->view->finish();
	        $dataList->content = $this->view->getContent();
	        $this->sendSuccessResult($dataList);
	    }
	}
	/**
	 *
	|+----------------------------------------
	| 添加公司
	|+----------------------------------------
	 */
	public function newAction()
	{
	    $this->dispatcher->forward(
	        [
	            "controller" => "company",
	            "action" => "edit"
	        ]);
	}
	/**
	 *
	|+----------------------------------------
	| 编辑公司
	|+----------------------------------------
	 */
	public function editAction()
	{
	    $itemId  = isset($_REQUEST['id'])?intval($_REQUEST['id']):'';
	    $backUrl = $this->getHelper()->createUrl(array('p'=>'company/index'));
	    $moduleAuth = array();
	    if($itemId>0){
	        $item = CompanyModel::factory()->findFirst('id='.$itemId);
	        
	        if(empty($item))
	        {
	            Utils::showMsg('修改的记录不存在!',$backUrl);
	        }
	        $item->expire_time = $item->expire_time!=-1?$this->getHelper()->getTime()->localDate('Y-m-d H:i:s',$item->expire_time):'';

	        $this->view->setVar('item', $item);
	        $moduleAuth = CompanyModuleAuthModel::getCompanyAuthMap($itemId);
	    }
	    $this->view->setVar('moduleAuth', $moduleAuth);
	    $this->view->setVar('moduleViewList', CompanyModuleAuthModel::buildModuleViewList($moduleAuth));
	    
	}
	/**
	 *
	|+----------------------------------------
	| 更新公司  添加公司 自动生成一个管理员
	|+----------------------------------------
	 */
	public function saveAction()
	{
	    $backUrl = $this->getHelper()->createUrl(array('p'=>'company/index'));
	    if($this->request->isPost())
	    {
	        $postData = $_POST;
	        if(empty($postData['name']))
	        {
	            Utils::showMsg('请填写公司名称!',$backUrl);
	        }
	        
	        $postData['expire_time'] = $postData['expire_time']?$this->getHelper()->getTime()->localStrtotime($postData['expire_time']):-1;
	        
	        $where = 'name="'.$postData['name'].'"';
	        if(!empty($postData['id'])){
	            $where .= ' and id!='.$postData['id'];
	        }
	        //检查是否存在
	        $check = CompanyModel::factory()->findFirst($where);
	        if($check){
	            Utils::showMsg('公司已存在，请重新填写!',$backUrl);
	        }
	        
	        $appPlatform = empty($postData['app_platform']) ? 'dingding' : $postData['app_platform'];
	        if(!in_array($appPlatform, array('dingding', 'wecom', 'feishu', 'manual'))){
	            $appPlatform = 'dingding';
	        }
	        
	        $data = array(
	            'name'    => $postData['name'],
	            'contact' => $postData['contact'],
	            'phone'   => $postData['phone'],
	            'address' => $postData['address'],
	            'remark'  => $postData['remark'],
	            'status'  => intval($postData['status']),
	            'expire_time'=>  $postData['expire_time'],
	        	'industry' => $postData['industry'],
	        	'app_platform' => $appPlatform,
	        	'user_id' => $postData['user_id'],
	        	'corpsecret' => trim($postData['corpsecret']),
	        	'corpid' => $postData['corpid'],
	        	'personlimit' => $postData['personlimit'],
	        	'reportlimit' => $postData['reportlimit'],
	        	'reporttpllimit' => $postData['reporttpllimit'],
	        	'agentid' => intval($postData['agentid']),
	            'ssosecret' => trim($postData['ssosecret']),
	        	'pointstatus'=>intval($postData['pointstatus'])	
	        );

	        $savedCompanyId = 0;
	        if(empty($postData['id'])){
	        	$nowtime = $this->getHelper()->getTime()->gmtime() ;
	        	
	        	//生成新公司的时候 判断手机号码是否已经存在
	        	$phoneisexists = CompanyModel::findFirstByPhone($this->request->get('phone')) ;
	        	if($phoneisexists)
	        	{
	        		Utils::showMsg('此手机号码已经存在，请重新输入',$backUrl);
	        	}
	            $data['created'] = $nowtime ;
	            $data['hash_key'] = md5(microtime(true));
	            
	            //新建公司 默认状态为试用期 使用时间一个月
	            $data['status'] = 1 ;
	            $data['expire_time'] = $nowtime + 2592000;
	            $companyModel = CompanyModel::factory();
	            $result = $companyModel->save($data);
	            if($result)
	            {
	            	$savedCompanyId = $companyModel->id ;
	            	$data['company_id'] = $companyModel->id ;
	            	UserModel::createUser($data) ;
	            }
	        }else{
	        	
	            $data['id'] = intval($postData['id']);
	            
	            $item = CompanyModel::factory()->findFirst('id='.$data['id']);
	            if(empty($item))
	            {
	                Utils::showMsg('修改的记录不存在!',$backUrl);
	            }
	            $result =$item->save($data);
	            if($result)
	            {
	                $savedCompanyId = $data['id'];
	            }
	        }
	        if($result && $savedCompanyId){
	            $adminUser = AdminUserModel::getLoginUser();
	            $operatorId = empty($adminUser) ? 0 : intval($adminUser->user_id);
	            $moduleAuth = isset($postData['module_auth']) ? $postData['module_auth'] : array();
	            CompanyModuleAuthModel::saveCompanyAuth($savedCompanyId, $moduleAuth, $operatorId);
	            AdminLogModel::factory()->addLog('更新企业模块授权，公司ID：' . $savedCompanyId, $operatorId);
	        }
	        if($result){
	            Utils::showMsg('操作成功!',$backUrl);
	        }else{
	            Utils::showMsg('操作失败!',$backUrl);
	        }
	        
	    }else
	    {
	        Utils::showMsg('不支持的请求方式!',$backUrl);
	    }
	}
	
	
	
	protected function _getDataList()
	{
	    /*条件*/
	    $page = isset($_REQUEST['page'])?intval($_REQUEST['page']):1;
	    $page = $page<1?1:$page;
	    $pagesize = isset($_REQUEST['pagesize'])?intval($_REQUEST['pagesize']):15;
	    $where = '1=1';

	    $dataList = new \stdClass();
	    $filter   = array();
	    $filter['page'] = $page;
	    $filter['pagesize'] = $pagesize;
	    
	    $method    = trim($_REQUEST['filter']);
	    $keywords  = trim($_REQUEST['keywords']);
	    
	    if($method && $keywords){
	        $where .= ' and '.$method.' like "%'.$keywords.'%"';
	        $filter['filter']  = $method;
	        $filter['keywords']= $keywords;
	    }
	    
	    /*统计*/
	    $countInfo = $this->modelsManager->createBuilder()
	    									->columns('count(*) as num')
	    									->addFrom('ScshuxCms\Dacang\Model\CompanyModel','c')
	    									->where($where)
	    									->getQuery()
	    									->execute() ;
	    
	    $dataList->count = $countInfo[0]->num;
	    $dataList->currentPage = $page;
	    $dataList->pageSize = $pagesize;
	    $dataList->pageCount= ceil($dataList->count/$dataList->pageSize);
	    
	    
	    /*加载数据*/
	    $offset = ($page-1)*$pagesize;    
	    
	    $columns = 'c.id,c.name,c.contact,c.phone,c.industry,c.app_platform,c.hash_key,c.status,c.expire_time,c.remark,sum(u.login_num) as loginnum';
	    $items = $this->modelsManager->createBuilder()
									    ->columns($columns)
									    ->addFrom('ScshuxCms\Dacang\Model\CompanyModel','c')
									    ->leftJoin('ScshuxCms\User\Model\UserModel','c.id = u.company_id','u')
									    ->groupBy('c.id')
									    ->where($where)
									    ->orderBy('c.id desc')
									    ->limit($pagesize,$offset)
									    ->getQuery()
									    ->execute() 
	    								->toArray();
	    
	    
	    if($items){
	        $companyIds = array();
	        foreach($items as $item){
	            $companyIds[] = intval($item['id']);
	        }
	        $salaryEnabledCompanies = CompanyModuleAuthModel::getEnabledCompanies($companyIds, 'salary');
	        $statusarr = array(
	            '0' => '未激活',
	            '1' => '试用期',
	            '2' => '正常'
	        );
	        $platformLabels = array(
	            'dingding' => '钉钉',
	            'wecom' => '企业微信',
	            'feishu' => '飞书',
	            'manual' => '手工/Excel'
	        );
	        foreach($items as $key=>$item){
	            $platform = empty($item['app_platform']) ? 'dingding' : $item['app_platform'];
	            $item['expire_time'] = $item['expire_time'] > -1 ? $this->getHelper()->formatDateTime($item['expire_time']) : '永不过期';
	            $item['status']      = $statusarr[$item['status']];
	            $item['salary_status'] = isset($salaryEnabledCompanies[intval($item['id'])]) ? '已开通' : '未开通';
	            $item['app_platform_label'] = isset($platformLabels[$platform]) ? $platformLabels[$platform] : $platform;
	            $items[$key] = $item;

	        }
	    }
	    
	    $items = arrayToObject(toLevel($items));

	    $dataList->items = $items;
	    $dataList->filter= $filter;
	    
	    return $dataList;
	}
	/**
	 * 删除数据
	 * @param  $ids
	 */
	protected  function  _remove($ids)
	{
	    if($ids){
	        $items = CompanyModel::factory()->find('id in('.$ids.')');
	        $db = FactoryDefault::getDefault()->getdb() ;
	        foreach ($items as $item){
	            $bool = $item->delete();
	            //删除相关的用户帐号
	            $companyId = $item->id ;
	            $table = UserModel::factory()->getSource() ;
	            $sql = 'delete from '.$table.' where company_id = '.$companyId;
	            $db->query($sql);
	            //删除compnayuser相关数据
	            $companyusertable = CompanyUserModel::factory()->getSource();
	            $sqll = 'delete from '.$companyusertable.' where company_id = '.$companyId;
	            $db->query($sqll);
	        }
	    }
	}
	
}
