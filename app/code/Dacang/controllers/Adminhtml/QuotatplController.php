<?php
/**
 * 指标库管理
*/
namespace ScshuxCms\Adminhtml\Controller;

use ScshuxCms\Core\Controller\AdminBaseController;
use ScshuxCms\Core\Controller\FrontendBaseController;
use ScshuxCms\Dacang\Model\QuotatplModel;
use ScshuxCms\Core\Helper\Utils;
use ScshuxCms\Core\Constants;
use ScshuxCms\Dacang\Model\CompanyDepartModel;
use ScshuxCms\Dacang\Model\DepartmentModel;
use Phalcon\Di\FactoryDefault;
use ScshuxCms\Core\Helper;
use ScshuxCms\Dacang\Model\ReportModel;
use ScshuxCms\Dacang\Model\ReportItemModel;
use ScshuxCms\Core\Tree;

class QuotatplController extends AdminBaseController
{
	/**
	 *
	 * @desc 指标列表
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

		$this->view->setVar('dataList', $dataList);
		$this->view->setVar('full_page',1);
		$this->view->setVar('quotatype', Constants::getQuotaType()) ;
		if($isAjax)
		{
			$this->view->setMainView(false);
			$this->view->start();
			$this->view->setVar('full_page',0);
			$this->view->render('quotatpl','index');
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
		$itemId = isset($_REQUEST['id'])?intval($_REQUEST['id']):'';
		if($itemId>0){
			$item = QuotatplModel::factory()->findFirst('id='.$itemId);
			if(empty($item))
			{
				Utils::showMsg('修改的记录不存在!',$backUrl);
			}
				
			$this->view->setVar('item', $item);
		}
	}


	/**
	 *
	 * @desc 指标保存
	 * @date 2017年4月1日
	 */
	public function saveAction()
	{
		$backUrl = $this->getHelper()->createUrl(array('p'=>'quotatpl/index'));
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
					'type'   => isset($postData['type']) ? intval($postData['type']) : 1
			);
				
			//判断是修改还是添加
			$id = intval($postData['id']) ;
			if(empty($id)){
				//判断名称是否存在  同一个commanyId下的指标名称不能重复
				$isTrue = QuotatplModel::findFirst(" name = '".trim($postData['name']."'"));
				if($isTrue)
				{
					Utils::showMsg('当前指标名称已经存在， 请从新输入',$backUrl);
				}

				$result = QuotatplModel::factory()->saveData($data);

			}else{
				$item = QuotatplModel::factory()->findFirst('id='.$id);
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
	 * @desc	上传excel
	 * @date	2017年5月12日
	 */
	public function UploadExcelAction ()
	{
		$gourl = Helper::factory()->createUrl(array('p'=>'quotatpl/index')) ;
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
					'name'       => 'A',
					'type'       => 'B',
					'point_desc' => 'C',
					'remark'	 => 'D'
			);
			//调用phpexcel类   读取excel 文件
			$data = Utils::readExcel(WEBROOT.$file, $array) ;
			if(!$data)
			{
				Utils::showMsg('读取指标错误', $gourl) ;
			}
			//根据获取的data数据  添加到mysql
			if($this->createQuota($data))
			{
				Utils::showMsg('上传指标成功', $gourl) ;
			}
			else
			{
				Utils::showMsg('上传指标失败', $gourl) ;
			}
		}
		else
		{
				
			Utils::showMsg('请使用post方式上传', $gourl) ;
		}
	}

	/**
	 *
	 * @desc	导出excel模版
	 * @date	2017年5月12日
	 */
	public function exportExcelTplAction ()
	{
		ob_clean();
		$filename = WEBROOT.'/data/quotatpls.xls';
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
	 * @desc 获取指标列表
	 * @date 2017年4月1日
	 */
	protected  function _getDataList ()
	{
		/*条件*/
		$page = isset($_REQUEST['page'])?intval($_REQUEST['page']):1;
		$page = $page<1?1:$page;
		$pagesize = isset($_REQUEST['pagesize'])?intval($_REQUEST['pagesize']):15;
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
			$items = QuotatplModel::factory()->deleteBySql('id in('.$ids.')');
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
		if(!$data || !is_array($data))
		{
			return  $return ;
		}

		//构建sql
		$sql = "insert into \ScshuxCms\Dacang\Model\QuotatplModel (name, point_desc, type, remark) ";
		$sql.= 'values (:name:,:point_desc:,:type:,:remark:)';


		foreach ($data as $value)
		{
			if (!is_string($value['point_desc']))
			{
				continue ;
			}
				

			$type = 1 ;
			if($value['type'] && in_array($value['type'],array('1','2','3')))
			{
				$type = $value['type'] ;
			}
				
			$this->modelsManager->executeQuery($sql,array(
					'name'    	 => $value['name'],
					'point_desc' => $value['point_desc'],
					'type' 		 => $type,
					'remark'	 => $value['remark']
			)) ;
				
		}

		return true ;
	}


}