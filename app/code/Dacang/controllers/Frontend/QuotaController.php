<?php 
/**
 * 指标库管理
 */
namespace ScshuxCms\Frontend\Controller;

use ScshuxCms\Core\Controller\FrontendBaseController;
use ScshuxCms\Dacang\Model\QuotaModel;
use ScshuxCms\Core\Helper\Utils;
use ScshuxCms\Core\Constants;
use ScshuxCms\Dacang\Model\CompanyDepartModel;
use ScshuxCms\Dacang\Model\DepartmentModel;
use Phalcon\Di\FactoryDefault;
use ScshuxCms\Core\Helper;
use ScshuxCms\Dacang\Model\ReportModel;
use ScshuxCms\Dacang\Model\ReportItemModel;
use ScshuxCms\Core\Tree;
use ScshuxCms\Dacang\Model\QuotatplModel;
use ScshuxCms\Dacang\Model\QuotaCommentModel;
use ScshuxCms\User\Model\UserManageRoleModel;

class QuotaController extends FrontendBaseController
{
	
	protected static $_callbankarr = array() ;
	
	/**
	 * 
	 * @desc 指标列表   只读取当前公司下的指标
	 * @date 2017年4月1日
	 */
	public  function  indexAction()
	{	
		$act = isset($_REQUEST['act']) ? $_REQUEST['act'] : '';
		$isAjax = isset($_REQUEST['is_ajax']) ? $_REQUEST['is_ajax'] : false;
		if($act == 'remove'){
			$this->_remove($_REQUEST['id']);
		}
		 
		$dataList = $this->_getDataList();
		$departlist = $this->parseDepart(DepartmentModel::TreeDepartList($this->companyId)) ;
		$this->view->setVar('dataList', $dataList);
		$this->view->setVar('full_page',1);
		$this->view->setVar('quotatype', Constants::getQuotaType()) ;
		$this->view->setVar('departlist', $departlist) ;
		if($isAjax)
		{
			$this->view->setMainView(false);
			$this->view->start();
			$this->view->setVar('full_page',0);
			$this->view->render('quota','index');
			$this->view->finish();
			$dataList->content = $this->view->getContent();
			$this->sendSuccessResult($dataList);
		}
		
	}
	
	/**
	 * 
	 * @desc 指标编辑 
	 * @date 2017年4月1日
	 */
	public function editAction()
	{
		DepartmentModel::factory()->departListOne($this->companyId);
		$itemId = isset($_REQUEST['id'])?intval($_REQUEST['id']):'';
		if($itemId>0){
			$item = QuotaModel::factory()->findFirst('id='.$itemId);
			if(empty($item))
			{
				Utils::showMsg('修改的记录不存在!',$backUrl);
			}
			
			$this->view->setVar('item', $item);
		}
		
		//获取部门列表平
		$departlist = DepartmentModel::departList($this->companyId) ;
		
		$this->view->setVar('departlist', $departlist) ;
		
	}
	
	
	/**
	 * 
	 * @desc 指标保存 
	 * @date 2017年4月1日
	 */
	public function saveAction()
	{	
		$backUrl = $this->getHelper()->createUrl(array('p'=>'quota/index'));
		if($this->request->isPost())
		{
			$postData = $_POST;
			
			if(empty($postData['name']))
			{
				Utils::showMsg('请添加指标名称!',$backUrl);
			}
			if(empty($postData['point_desc']))
			{
				Utils::showMsg('请添加指标的评分说明!',$backUrl);
			}
			
			//封装数据
			$data = array(
					'name'   => trim($postData['name']),
					'remark' => isset($postData['remark']) ? trim($postData['remark']) : '',
					'point_desc' => trim($postData['point_desc']),
					'type'   => isset($postData['type']) ? intval($postData['type']) : 1,
					'depart_id'  => intval($postData['depart_id']),
			);
			
			//判断是修改还是添加
			$id = intval($postData['id']) ;
			if(empty($id)){
				//判断名称是否存在  同一个commanyId下的指标名称不能重复
				$isTrue = QuotaModel::findFirst("company_id = ".$this->companyId." and name = '".trim($postData['name']."'"));
				if($isTrue)
				{
					Utils::showMsg('当前指标名称已经存在， 请从新输入',$backUrl);
				}
				
				$data['company_id'] = $this->companyId;
				
				$result = QuotaModel::factory()->saveData($data);
				
			}else{
				$item = QuotaModel::factory()->findFirst('id='.$id);
				if(empty($item))
				{
					Utils::showMsg('修改的记录不存在!',$backUrl);
				}
				$result = $item->saveData($data);
			}
			if($result){
				Utils::showMsg('操作成功!',$backUrl);
			}else{
				Utils::showMsg('操作失败!',$backUrl);
			}
		
		}
		else
		{
			Utils::showMsg('不支持的请求方式!',$backUrl);
		}
	}
	
	
	
	/**	
	 *
	 * @desc 简单指标列表   只读取当前公司下的指标
	 * @date 2017年4月1日
	 */
	public  function  simpleListAction()
	{
		$act = isset($_REQUEST['act']) ? $_REQUEST['act'] : '';
		$isAjax = isset($_REQUEST['is_ajax']) ? $_REQUEST['is_ajax'] : false;
		
		$dataList = $this->_getDataList();
		$this->view->setVar('dataList', $dataList);
		$this->view->setVar('full_page',1);
		$this->view->setVar('quotatype', Constants::getQuotaType()) ;
		$this->view->setVar('departlist', DepartmentModel::departList($this->companyId)) ;
		if($isAjax)
		{
			$this->view->setMainView(false);
			$this->view->start();
			$this->view->setVar('full_page',0);
			$this->view->render('quota','simplelist');
			$this->view->finish();
			$dataList->content = $this->view->getContent();
			$this->sendSuccessResult($dataList);
		}
	
	}
	
	
	
	
	/**
	 *
	 * @desc 获取系统默认 指标模版
	 * @date 2017年4月1日
	 */
	public  function  quotaTplAction()
	{
		$act = isset($_REQUEST['act']) ? $_REQUEST['act'] : '';
		$isAjax = isset($_REQUEST['is_ajax']) ? $_REQUEST['is_ajax'] : false;
	
		$dataList = $this->_getTplDataList();
	
		$this->view->setVar('dataList', $dataList);
		$this->view->setVar('full_page',1);
		$this->view->setVar('quotatype', Constants::getQuotaType()) ;
		$this->view->setVar('departlist', DepartmentModel::departList($this->companyId)) ;
		if($isAjax)
		{
			$this->view->setMainView(false);
			$this->view->start();
			$this->view->setVar('full_page',0);
			$this->view->render('quota','quotatpl');
			$this->view->finish();
			$dataList->content = $this->view->getContent();
			$this->sendSuccessResult($dataList);
		}
	
	}
	
	
	
	/**
	 *
	 * @desc 获取系统默认 指标模版
	 * @date 2017年4月1日
	 */
	public  function  addquotaAction()
	{
		$isajax = $this->request->isAjax();
		$ispost = $this->request->isPost();
		if ($isajax && $ispost)
		{
			$departid = $this->request->get('departid') ;	//部门
			$quotaids = $this->request->get('quotaids') ;	//指标id

			if (!$departid || !$quotaids)
			{
				$this->sendErrorResult('参数错误') ;
			}
			
			$departid = intval($departid) ;
			$quotaids = addslashes($quotaids) ;
			
			$departinfo = DepartmentModel::factory()->findFirst('id = '.$departid.' and company_id = '.$this->companyId) ;
			if (!$departinfo)
			{
				$this->sendErrorResult('部门不存在') ;
			}
			
			$quotainfo = QuotatplModel::find('id in ('.$quotaids.')') ;
			if (!$quotainfo)
			{
				$this->sendErrorResult('指标不存在') ;
			}
			
			
			foreach ($quotainfo as $quota)
			{
				$quotaname = $quota->name ;
				$exists = QuotaModel::findFirst('name = "'.$quotaname.'" and company_id = '.$this->companyId) ;
				if ($exists)
				{
					continue ;
				}
				
				
				$data = array(
						'name'   => $quota->name,
						'remark' => $quota->remark,
						'point_desc' => $quota->point_desc,
						'type'   => $quota->type,
						'depart_id'  => $departid,
						'company_id' => $this->companyId
				);
				
				QuotaModel::factory()->saveData($data) ;
				QuotaModel::delFactory() ;
			}
			$this->sendSuccessResult('成功') ;
		}
	
	}
	
	/**
	 * 
	 * @desc	上传excel
	 * @date	2017年5月12日
	 */
	public function UploadExcelAction()
	{
		$gourl = Helper::factory()->createUrl(array('p'=>'quota/index')) ;
		$ispost = $this->request->isPost();
		if($ispost)
		{
			//判断文件类型
			if($_FILES['exceltpl']['name'])
			{
				$extname = array_pop(explode('.', $_FILES['exceltpl']['name']))	 ;	
				if(!in_array($extname, array('xls','xlsx')))
				{
					Utils::showMsg('请上传excel文件', $gourl) ;
				}
			}
			else 
			{
				Utils::showMsg('请上传文件', $gourl) ;
			}
			
			$file = Utils::uploadFile('exceltpl','excel','xls');
			if(!$file)
			{
				Utils::showMsg('文件不存在，请从新上传', $gourl) ;
			}
			
			//构建字段 => 位置 数组
			$array = array(
					'departname' => 'A',
					'name'       => 'B',
					'type'       => 'C',
					'point_desc' => 'D',
			);
			//判断是否需要回调
			$callbank = $this->request->get('callbank') ;
			//增加导入权重项
			if ($callbank == 'true')
			{
				$array['quota_weight'] = 'E';
			}
			
			//调用phpexcel类   读取excel 文件
			$data = Utils::readExcel(WEBROOT.$file, $array) ;
			if(!$data)
			{
				Utils::showMsg('读取指标错误', $gourl) ;
			}
			
			//根据获取的data数据  添加到mysql
			if($this->createQuota($data))
			{
				if ($callbank == 'true')
				{
					//将传入的指标数据   封装成需要的格式  返回
					$callbankarr = self::$_callbankarr ;
					
					$this->sendSuccessResult($callbankarr) ;
				}
				Utils::showMsg('上传指标成功', $gourl) ;
			}
			else 
			{
				Utils::showMsg('上传指标失败', $gourl) ;
			}
		}
		else 
		{
			
			Utils::showMsg('error', $gourl) ;
		}
	}
	
	/**
	 * 
	 * @desc	导出excel模版	
	 * @date	2017年5月12日
	 */
	public function exportExcelTplAction()
	{
		ob_clean();
		$filename = WEBROOT.'/data/quotatpl.xls';
		$name = '指标模版';
		if(file_exists($filename)){
			$content = file_get_contents($filename);
			header("Content-type:application/vnd.ms-excel");
			header("Content-Disposition:filename=".$name.'.xls');
			
			echo $content;
		}
		exit() ;
		
	}
	
	
	
	/**
	 *
	 * @desc	新建指标的时候   导出excel模版
	 * @date	2017年5月12日
	 */
	public function exportReportExcelTplAction()
	{
		ob_clean();
		$filename = WEBROOT.'/data/new_report_quotatpl.xls';
		$name = '指标模版';
		if(file_exists($filename)){
			$content = file_get_contents($filename);
			header("Content-type:application/vnd.ms-excel");
			header("Content-Disposition:filename=".$name.'.xls');
				
			echo $content;
		}
		exit() ;
	
	}
	
	
	/**
	 * @desc	指标点评
	 * @param
	 * @return
	 */
	public function quota_commentAction()
	{
		$act = isset($_REQUEST['act']) ? $_REQUEST['act'] : '';
		$isAjax = isset($_REQUEST['is_ajax']) ? $_REQUEST['is_ajax'] : false;
		if($act == 'remove'){
			$this->_remove_comment($_REQUEST['id']);
		}
			
		$dataList = $this->_getCommentDataList();
		$this->view->setVar('dataList', $dataList);
		$this->view->setVar('full_page',1);
		if($isAjax)
		{
			$this->view->setMainView(false);
			$this->view->start();
			$this->view->setVar('full_page',0);
			$this->view->render('quota','quota_comment');
			$this->view->finish();
			$dataList->content = $this->view->getContent();
			
			
			$this->sendSuccessResult($dataList);
		}
		
	}
	
	/**
	 * 
	 * @desc 获取指标列表 
	 * @date 2017年4月1日
	 */
	protected  function _getDataList()
	{
		/*条件*/
		$page = isset($_REQUEST['page'])?intval($_REQUEST['page']):1;
		$page = $page<1?1:$page;
		$pagesize = isset($_REQUEST['pagesize'])?intval($_REQUEST['pagesize']):15;
		$filter = array();
		
		$where = ' company_id = '.$this->companyId;	
		$andwhere=UserManageRoleModel::factory()->getWhereByUserManageRole();
		$where = $andwhere?$where.' and '.$andwhere:$where;
		
		//积分考核模式 只需要查找 权重制和加减分
		if ($_REQUEST['type'] && $_REQUEST['type']=='point')
		{
			$filter['type'] = trim($_REQUEST['type']);
			$where .= ' and type in(3,4)';
		}
		if($_REQUEST['name']){	
			$filter['name'] = trim($_REQUEST['name']);
			$where .= " and  name  like '%{$filter['name']}%'";
		}
		if($_REQUEST['depart_id']){
			$filter['depart_id'] = trim($_REQUEST['depart_id']);
			$where .= " and  depart_id  = ".$filter['depart_id'];
		}
		$dataList = new \stdClass();
	
		/*统计*/
		$countInfo = QuotaModel::query()
							->where($where)
							->columns('count(*) as num')
							->execute();

		$dataList->count       = isset($countInfo[0]) ? $countInfo[0]->num : 0 ;
		$dataList->currentPage = $page;
		$dataList->pageSize    = $pagesize;
		$dataList->pageCount   = ceil($dataList->count/$dataList->pageSize);
		$dataList->filter      = $filter ;
		/*加载数据*/
		
		
		$offset = ($page-1)*$pagesize;
		$items = QuotaModel::query()
							->where($where)
							->orderBy('depart_id desc')
							->limit($pagesize,$offset)
							->execute()
							->toArray();
		
		$dataList->items = $items;
		return $dataList;
		
	}
	
	
	
	/**
	 *
	 * @desc 获取默认指标列表
	 * @date 2017年4月1日
	 */
	protected  function _getTplDataList()
	{
		/*条件*/
		$page = isset($_REQUEST['page'])?intval($_REQUEST['page']):1;
		$page = $page<1?1:$page;
		$pagesize = isset($_REQUEST['pagesize'])?intval($_REQUEST['pagesize']):10;
		$filter = array();
	
		$where = ' 1 = 1 ';
	
		if($_REQUEST['name']){
			$filter['name'] = trim($_REQUEST['name']);
			$where .= " and  name  like '%{$filter['name']}%'";
		}
	
		$dataList = new \stdClass();
	
		/*统计*/
		$countInfo = QuotatplModel::query()
								->where($where)
								->columns('count(*) as num')
								->execute();
	
		$dataList->count       = isset($countInfo[0]) ? $countInfo[0]->num : 0 ;
		$dataList->currentPage = $page;
		$dataList->pageSize    = $pagesize;
		$dataList->pageCount   = ceil($dataList->count/$dataList->pageSize);
		$dataList->filter      = $filter ;
		/*加载数据*/
	
	
	
		$offset = ($page-1)*$pagesize;
		$items = QuotatplModel::query()
								->where($where)
								->orderBy('id desc')
								->limit($pagesize,$offset)
								->execute()
								->toArray();
	
		$dataList->items = $items;
		return $dataList;
	
	}
	
	
	
	/**
	 * 删除数据
	 * @param  $ids
	 */
	protected  function  _remove($ids)
	{
		if($ids){
			$items = QuotaModel::factory()->find('id in('.$ids.')');
			foreach ($items as $item){
				
				//判断指标是否在使用中  使用中的指标 不予删除
				$istrue = ReportItemModel::findFirst('quota_id = '.$item->id) ;
				$istrue = false;
				if($istrue)
				{
					continue ;
				}
				else 
				{
					$item->delete();
				}
			}
		}
	}
	
	
	
	
	
	/**
	 * 
	 * @desc	生成指标
	 * @param	$data  指标数据	
	 * @return	bool	
	 * @date	2017年5月12日
	 */
	protected function createQuota($data)
	{
		$return  = false ;
		self::$_callbankarr = array();
		
		if(!$data || !is_array($data))
		{
			return  $return ;
		}
		
		//获取当前公司 公司所属的部门id
		$defaultid = 0 ;
		$default = CompanyDepartModel::findFirst('dingding_id = 1 and company_id = '.$this->companyId) ;
		if($default)
		{
			$defaultid = $default->id ;
		}
		
		$departname = '';
		$departid   = 0 ;
		$quotatype  = Constants::getQuotaType()	; 
		foreach ($data as $value)
		{
			if ($value['name'] == '')
			{
				continue ;
			}
			if (!is_string($value['point_desc']))
			{
				continue ;
			}
			//部门名称
			$tmpname = trim($value['departname']) ;
			if($departname == $tmpname)
			{
				$depart_id = $departid ;
			}
			else 
			{
				//根据部门名称获取id
				$departinfo = DepartmentModel::findFirst(' name = "'.$tmpname.'" and company_id = '.$this->companyId) ;
				if($departinfo)
				{
					$departid = $departinfo->id ;
				}
				else 
				{
					//默认归到当前的公司下
					$departid = $defaultid ;
				}
			}
		
			$type = 1 ;
			if($value['type'] && in_array($value['type'],array('1','2','3','4','5')))
			{
				$type = $value['type'] ;
			}
			
			//积分考评表 只能导入 权重制 加减分  
			$point=$this->request->get('point');
			if ($point && !in_array($type, array(3,4)))
			{
				continue;
			}
			$data = array(
					'name'    	 => $value['name'],
					'point_desc' => $value['point_desc'],
					'type' 		 => $type,
					'depart_id'  => $departid,
					'company_id' => $this->companyId
			) ;
			
			$quotamodel = QuotaModel::factory();
			$res = $quotamodel->saveData($data) ;
			if ($res)
			{
				self::$_callbankarr['id'] .= self::$_callbankarr['id'] ? ','.$quotamodel->id : $quotamodel->id ;
				self::$_callbankarr['quota'] .= self::$_callbankarr['quota'] ? ','.$quotamodel->name : $quotamodel->name ;
				self::$_callbankarr['quotatype'] .= self::$_callbankarr['quotatype'] ? ','.$quotatype[$quotamodel->type] : $quotatype[$quotamodel->type] ;
				self::$_callbankarr['quota_weight'] .= self::$_callbankarr['quota_weight'] ? ','.$value['quota_weight'] : $value['quota_weight'] ;
				self::$_callbankarr['quotatypeval'] .= self::$_callbankarr['quotatypeval'] ? ','.$quotamodel->type : $quotamodel->type ;
			}
		
			QuotaModel::delFactory() ;
			
		}
		
		return true ;
	}
	
	
	protected function parseDepart($obj)
	{
		$data = array() ;
		foreach ($obj as $o)
		{
			$data[$o->id] = $o;
		}
		
		return $data ;
	}
	
	
	/**
	 *
	 * @desc 获取指标点评列表
	 * @date 2017年4月1日
	 */
	protected  function _getCommentDataList()
	{
		/*条件*/
		$page = isset($_REQUEST['page'])?intval($_REQUEST['page']):1;
		$page = $page<1?1:$page;
		$pagesize = isset($_REQUEST['pagesize'])?intval($_REQUEST['pagesize']):15;
		$filter =array();
		$reportId= intval($this->request->get('report_id'));
		$quotaId = intval($this->request->get('quota_id'));
		
		$where .= ' qid = '.$quotaId.' and rid='.$reportId;
		$filter['report_id'] = $reportId;
		$filter['quota_id']  = $quotaId;
	
		if($_REQUEST['keyword']){
			$filter['keyword'] = trim($_REQUEST['keyword']);
			$where .= " and  content  like '%{$filter['keyword']}%'";
		}
		
		$dataList = new \stdClass();
	
		/*统计*/
		$countInfo = QuotaCommentModel::query()
		->where($where)
		->columns('count(*) as num')
		->execute();
	
		$dataList->count       = isset($countInfo[0]) ? $countInfo[0]->num : 0 ;
		$dataList->currentPage = $page;
		$dataList->pageSize    = $pagesize;
		$dataList->pageCount   = ceil($dataList->count/$dataList->pageSize);
		$dataList->filter      = $filter ;
		/*加载数据*/
	
		$offset = ($page-1)*$pagesize;
		$items = QuotaCommentModel::query()
		->where($where)
		->orderBy('id desc')
		->limit($pagesize,$offset)
		->execute()
		->toArray();
		
		$dataList->items = $items;
		return $dataList;
	
	}
	
	
	/**
	 * 删除点评
	 * @param  $ids
	 */
	protected  function  _remove_comment($ids)
	{
		if($ids){
			$ids = trim($ids);
			QuotaCommentModel::factory()->deleteBySql('id in('.$ids.')');
		}
	}
	
	
}