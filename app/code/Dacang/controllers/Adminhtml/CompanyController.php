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
use ScshuxCms\Core\Helper\Utils;
use ScshuxCms\User\Model\UserModel;
use Phalcon\Di\FactoryDefault;
use ScshuxCms\Dacang\Model\CompanyUserModel;
use ScshuxCms\Dacang\Model\PlatformIntegrationModel;
use ScshuxCms\Dacang\Helper\WecomCredential;
use ScshuxCms\Dacang\Helper\FeishuCredential;
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
	    $platform = isset($_REQUEST['platform'])?trim($_REQUEST['platform']):'dingding';
	    if(!in_array($platform, array('dingding', 'wecom', 'feishu', 'manual'))){
	        $platform = 'dingding';
	    }
	    $backUrl = $this->getHelper()->createUrl(array('p'=>'company/index'));
	    if($itemId>0){
	        $item = CompanyModel::factory()->findFirst('id='.$itemId);
	        
	        if(empty($item))
	        {
	            Utils::showMsg('修改的记录不存在!',$backUrl);
	        }
	        if(!empty($item->app_platform) && in_array($item->app_platform, array('dingding', 'wecom', 'feishu', 'manual'))){
	            $platform = $item->app_platform;
	        }
	        $item->expire_time = $item->expire_time!=-1?$this->getHelper()->getTime()->localDate('Y-m-d H:i:s',$item->expire_time):'';

	        $this->view->setVar('item', $item);
	    }
	    $this->view->setVar('platform', $platform);
	    
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
	        $platform = isset($postData['platform'])?trim($postData['platform']):'dingding';
	        if(!in_array($platform, array('dingding', 'wecom', 'feishu', 'manual'))){
	            $platform = 'dingding';
	        }
	        if(empty($postData['name']))
	        {
	            Utils::showMsg('请填写公司名称!',$backUrl);
	        }
	        if($platform == 'wecom'){
	            if(empty($postData['wecom_corp_id']) || empty($postData['wecom_agent_id']) || empty($postData['wecom_secret'])){
	                Utils::showMsg('请完整填写企业微信CorpID、AgentID和Secret!',$backUrl);
	            }
	        }
	        
	        if($platform == 'feishu'){
	            if(empty($postData['feishu_app_id']) || empty($postData['feishu_app_secret'])){
	                Utils::showMsg('请完整填写飞书 App ID 和 App Secret!',$backUrl);
	            }
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
	        
	        $data = array(
	            'name'    => $postData['name'],
	            'contact' => $postData['contact'],
	            'phone'   => $postData['phone'],
	            'address' => $postData['address'],
	            'remark'  => $postData['remark'],
	            'status'  => intval($postData['status']),
	            'expire_time'=>  $postData['expire_time'],
	        	'industry' => $postData['industry'],
	            'app_platform' => $platform,
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
				$result = $companyModel->saveData($data);
	            if($result)
	            {
	            	$data['company_id'] = $companyModel->id ;
	            	UserModel::createUser($data) ;
	            	if($platform == 'wecom'){
	            	    $integration = new PlatformIntegrationModel();
	            	    $integration->company_id = $companyModel->id;
	            	    $integration->platform = 'wecom';
	            	    $integration->corp_id = trim($postData['wecom_corp_id']);
	            	    $integration->agent_id = trim($postData['wecom_agent_id']);
	            	    $integration->secret_enc = WecomCredential::encrypt(trim($postData['wecom_secret']));
	            	    $integration->callback_token = trim($postData['wecom_callback_token']);
	            	    $integration->encoding_aes_key = trim($postData['wecom_encoding_aes_key']);
	            	    $integration->enabled = 1;
	            	    $integration->created_at = $nowtime;
	            	    $integration->updated_at = $nowtime;
	            	    if(!$integration->save()){
	            	        Utils::showMsg('公司已创建，但企业微信参数保存失败，请在公司列表中重新配置!',$backUrl);
	            	    }
	            	    $backUrl = $this->getHelper()->createUrl(array(
	            	        'p'=>'wecom/index',
	            	        'company_id'=>$companyModel->id
	            	    ));
	            	}
			if($platform == 'feishu'){
			    $integration = new PlatformIntegrationModel();
			    $integration->company_id = $companyModel->id;
			    $integration->platform = 'feishu';
			    $integration->corp_id = trim($postData['feishu_app_id']);
			    $integration->agent_id = '';
			    $integration->secret_enc = FeishuCredential::encrypt(trim($postData['feishu_app_secret']));
			    $integration->callback_token = trim($postData['feishu_verification_token']);
			    $integration->encoding_aes_key = trim($postData['feishu_encrypt_key']);
			    $integration->enabled = 0;
			    $integration->created_at = $nowtime;
			    $integration->updated_at = $nowtime;
			    if(!$integration->save()){
			        Utils::showMsg('公司已创建，但飞书参数保存失败，请在公司列表中重新配置',$backUrl);
			    }
			    $backUrl = $this->getHelper()->createUrl(array(
			        'p'=>'feishu/index',
			        'company_id'=>$companyModel->id
			    ));
			}
	            }
	        }else{
	        	
	            $data['id'] = intval($postData['id']);
	            
	            $item = CompanyModel::factory()->findFirst('id='.$data['id']);
	            if(empty($item))
	            {
	                Utils::showMsg('修改的记录不存在!',$backUrl);
	            }
				$result =$item->saveData($data);
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
	    
	    $columns = 'c.id,c.name,c.contact,c.phone,c.industry,c.app_platform,c.hash_key,c.status,c.expire_time,c.remark,sum(u.login_num) as loginnum,max(pi.platform) as platform';
	    $items = $this->modelsManager->createBuilder()
									    ->columns($columns)
									    ->addFrom('ScshuxCms\Dacang\Model\CompanyModel','c')
									    ->leftJoin('ScshuxCms\User\Model\UserModel','c.id = u.company_id','u')
									    ->leftJoin('ScshuxCms\Dacang\Model\PlatformIntegrationModel','c.id = pi.company_id','pi')
									    ->groupBy('c.id')
									    ->where($where)
									    ->orderBy('c.id desc')
									    ->limit($pagesize,$offset)
									    ->getQuery()
									    ->execute() 
	    								->toArray();
	    
	    
	    if($items){
	        $statusarr = array(
	            '0' => '未激活',
	            '1' => '试用期',
	            '2' => '正常'
	        );
	        foreach($items as $key=>$item){
	            if(empty($item['platform'])){
	                $item['platform'] = !empty($item['app_platform']) ? $item['app_platform'] : 'dingding';
	            }
	            $item['expire_time'] = $item['expire_time'] > -1 ? $this->getHelper()->formatDateTime($item['expire_time']) : '永不过期';
	            $item['status']      = $statusarr[$item['status']];
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
