<?php
/**
 * Salary project management.
 */
namespace ScshuxCms\Adminhtml\Controller;

use ScshuxCms\Core\Controller\AdminBaseController;
use ScshuxCms\Core\Helper\Utils;
use ScshuxCms\Dacang\Model\CompanyModel;
use ScshuxCms\Salary\Model\SalaryProjectModel;
use ScshuxCms\Salary\Model\SalaryProjectTemplateModel;

class SalaryprojectController extends AdminBaseController
{
	public function indexAction()
	{
		$act = isset($_REQUEST['act']) ? $_REQUEST['act'] : '';
		$isAjax = isset($_REQUEST['is_ajax']) ? $_REQUEST['is_ajax'] : false;

		if ($act == 'remove') {
			$this->_remove($_REQUEST['id']);
		}

		$dataList = $this->_getDataList();
		$this->view->setVar('dataList', $dataList);
		$this->view->setVar('companies', $this->_getCompanies());
		$this->view->setVar('full_page', 1);

		if ($isAjax) {
			$this->view->setMainView(false);
			$this->view->start();
			$this->view->setVar('full_page', 0);
			$this->view->render('salaryproject', 'index');
			$this->view->finish();
			$dataList->content = $this->view->getContent();
			$this->sendSuccessResult($dataList);
		}
	}

	public function newAction()
	{
		$this->dispatcher->forward(array(
			"controller" => "salaryproject",
			"action" => "edit",
		));
	}

	public function editAction()
	{
		$itemId = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
		if ($itemId > 0) {
			$item = SalaryProjectModel::factory()->findFirst('id=' . $itemId . ' and deleted_at=0');
			if (empty($item)) {
				Utils::showMsg('工资项目不存在', $this->getHelper()->createUrl(array('p' => 'salaryproject/index')));
			}
			$item->direction = SalaryProjectModel::normalizeDirection($item->direction);
			$this->view->setVar('item', $item);
		}

		$this->view->setVar('companies', $this->_getCompanies());
		$this->view->setVar('sourceTypes', SalaryProjectModel::getSourceTypeLabels());
		$this->view->setVar('directions', SalaryProjectModel::getDirectionLabels());
		$this->view->setVar('calculationModes', SalaryProjectModel::getCalculationModeLabels());
		$this->view->setVar('statusLabels', SalaryProjectModel::getStatusLabels());
	}

	public function saveAction()
	{
		$backUrl = $this->getHelper()->createUrl(array('p' => 'salaryproject/index'));
		if (!$this->request->isPost()) {
			Utils::showMsg('不支持的请求方式', $backUrl);
		}

		$postData = $_POST;
		$companyId = isset($postData['company_id']) ? intval($postData['company_id']) : 0;
		$name = isset($postData['name']) ? trim($postData['name']) : '';

		if ($companyId <= 0 || $name == '') {
			Utils::showMsg('请选择企业并填写工资项目名称', $backUrl);
		}

		$sourceTypes = SalaryProjectModel::getSourceTypeLabels();
		$directions = SalaryProjectModel::getDirectionLabels();
		$calculationModes = SalaryProjectModel::getCalculationModeLabels();
		$statusLabels = SalaryProjectModel::getStatusLabels();

		$sourceType = isset($postData['source_type']) ? trim($postData['source_type']) : 'fixed';
		$direction = isset($postData['direction']) ? SalaryProjectModel::normalizeDirection($postData['direction']) : 'earning';
		$calculationMode = isset($postData['calculation_mode']) ? trim($postData['calculation_mode']) : 'manual';
		$status = isset($postData['status']) ? trim($postData['status']) : 'active';

		if (!isset($sourceTypes[$sourceType]) || !isset($directions[$direction]) || !isset($calculationModes[$calculationMode]) || !isset($statusLabels[$status])) {
			Utils::showMsg('工资项目参数不正确', $backUrl);
		}

		$id = isset($postData['id']) ? intval($postData['id']) : 0;
		$duplicateWhere = 'company_id=' . $companyId . ' and name="' . addslashes($name) . '" and deleted_at=0';
		if ($id > 0) {
			$duplicateWhere .= ' and id!=' . $id;
		}
		$duplicate = SalaryProjectModel::factory()->findFirst($duplicateWhere);
		if ($duplicate) {
			Utils::showMsg('同一企业下已经存在相同工资项目', $backUrl);
		}

		$now = $this->getHelper()->getTime()->gmtime();
		$data = array(
			'company_id' => $companyId,
			'name' => $name,
			'source_type' => $sourceType,
			'direction' => $direction,
			'calculation_mode' => $calculationMode,
			'linked_module' => isset($postData['linked_module']) ? trim($postData['linked_module']) : 'none',
			'formula_text' => isset($postData['formula_text']) ? trim($postData['formula_text']) : '',
			'include_earning' => isset($postData['include_earning']) ? intval($postData['include_earning']) : 0,
			'include_deduction' => isset($postData['include_deduction']) ? intval($postData['include_deduction']) : 0,
			'include_net' => isset($postData['include_net']) ? intval($postData['include_net']) : 1,
			'sort_order' => isset($postData['sort_order']) ? intval($postData['sort_order']) : 0,
			'status' => $status,
			'updated_at' => $now,
		);

		if ($id > 0) {
			$item = SalaryProjectModel::factory()->findFirst('id=' . $id . ' and deleted_at=0');
			if (empty($item)) {
				Utils::showMsg('工资项目不存在', $backUrl);
			}
			$result = $item->save($data);
		} else {
			$data['created_at'] = $now;
			$result = SalaryProjectModel::factory()->save($data);
		}

		Utils::showMsg($result ? '操作成功' : '操作失败', $backUrl);
	}

	public function initAction()
	{
		$companyId = isset($_REQUEST['company_id']) ? intval($_REQUEST['company_id']) : 0;
		$backUrl = $this->getHelper()->createUrl(array('p' => 'salaryproject/index', 'company_id' => $companyId));
		if ($companyId <= 0) {
			Utils::showMsg('请先选择企业', $this->getHelper()->createUrl(array('p' => 'salaryproject/index')));
		}

		$company = CompanyModel::factory()->findFirst('id=' . $companyId);
		if (empty($company)) {
			Utils::showMsg('企业不存在', $this->getHelper()->createUrl(array('p' => 'salaryproject/index')));
		}

		$templates = SalaryProjectTemplateModel::factory()->find(array(
			'conditions' => 'status="active"',
			'order' => 'sort_order asc,id asc',
		));
		$now = $this->getHelper()->getTime()->gmtime();
		$created = 0;

		foreach ($templates as $template) {
			$exists = SalaryProjectModel::factory()->findFirst('company_id=' . $companyId . ' and template_id=' . intval($template->id) . ' and deleted_at=0');
			if ($exists) {
				continue;
			}

			$model = new SalaryProjectModel();
			$result = $model->save(array(
				'company_id' => $companyId,
				'template_id' => intval($template->id),
				'name' => $template->name,
				'source_type' => $template->source_type,
				'direction' => $template->direction,
				'calculation_mode' => $template->calculation_mode,
				'linked_module' => $template->linked_module,
				'formula_text' => '',
				'include_earning' => intval($template->include_earning),
				'include_deduction' => intval($template->include_deduction),
				'include_net' => intval($template->include_net),
				'sort_order' => intval($template->sort_order),
				'status' => 'active',
				'created_at' => $now,
				'updated_at' => $now,
				'deleted_at' => 0,
			));
			if ($result) {
				$created++;
			}
		}

		Utils::showMsg('已初始化 ' . $created . ' 个通用工资项目', $backUrl);
	}

	protected function _getDataList()
	{
		$page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
		$page = $page < 1 ? 1 : $page;
		$pagesize = isset($_REQUEST['pagesize']) ? intval($_REQUEST['pagesize']) : 15;
		$where = 'p.deleted_at=0';
		$filter = array(
			'page' => $page,
			'pagesize' => $pagesize,
			'company_id' => isset($_REQUEST['company_id']) ? intval($_REQUEST['company_id']) : 0,
			'keywords' => isset($_REQUEST['keywords']) ? trim($_REQUEST['keywords']) : '',
		);

		if ($filter['company_id'] > 0) {
			$where .= ' and p.company_id=' . $filter['company_id'];
		}
		if ($filter['keywords'] != '') {
			$where .= ' and p.name like "%' . addslashes($filter['keywords']) . '%"';
		}

		$countInfo = $this->modelsManager->createBuilder()
			->columns('count(*) as num')
			->addFrom('ScshuxCms\Salary\Model\SalaryProjectModel', 'p')
			->where($where)
			->getQuery()
			->execute();

		$dataList = new \stdClass();
		$dataList->count = $countInfo[0]->num;
		$dataList->currentPage = $page;
		$dataList->pageSize = $pagesize;
		$dataList->pageCount = ceil($dataList->count / $dataList->pageSize);

		$offset = ($page - 1) * $pagesize;
		$items = $this->modelsManager->createBuilder()
			->columns('p.id,p.company_id,p.name,p.source_type,p.direction,p.calculation_mode,p.linked_module,p.include_earning,p.include_deduction,p.include_net,p.sort_order,p.status,c.name as company_name')
			->addFrom('ScshuxCms\Salary\Model\SalaryProjectModel', 'p')
			->leftJoin('ScshuxCms\Dacang\Model\CompanyModel', 'p.company_id = c.id', 'c')
			->where($where)
			->orderBy('p.company_id asc,p.sort_order asc,p.id asc')
			->limit($pagesize, $offset)
			->getQuery()
			->execute()
			->toArray();

		$sourceTypes = SalaryProjectModel::getSourceTypeLabels();
		$directions = SalaryProjectModel::getDirectionLabels();
		$calculationModes = SalaryProjectModel::getCalculationModeLabels();
		$statusLabels = SalaryProjectModel::getStatusLabels();

		foreach ($items as $key => $item) {
			$item['direction'] = SalaryProjectModel::normalizeDirection($item['direction']);
			$item['source_type_label'] = SalaryProjectModel::label($sourceTypes, $item['source_type']);
			$item['direction_label'] = SalaryProjectModel::label($directions, $item['direction']);
			$item['calculation_mode_label'] = SalaryProjectModel::label($calculationModes, $item['calculation_mode']);
			$item['status_label'] = SalaryProjectModel::label($statusLabels, $item['status']);
			$items[$key] = $item;
		}

		$dataList->items = arrayToObject($items);
		$dataList->filter = $filter;
		return $dataList;
	}

	protected function _remove($ids)
	{
		if (!$ids) {
			return;
		}
		$ids = preg_replace('/[^0-9,]/', '', $ids);
		$ids = trim($ids, ',');
		if ($ids == '') {
			return;
		}
		$now = $this->getHelper()->getTime()->gmtime();
		$items = SalaryProjectModel::factory()->find('id in(' . $ids . ') and deleted_at=0');
		foreach ($items as $item) {
			$item->save(array(
				'deleted_at' => $now,
				'updated_at' => $now,
			));
		}
	}

	protected function _getCompanies()
	{
		return CompanyModel::factory()->find(array(
			'order' => 'id desc',
		));
	}
}
