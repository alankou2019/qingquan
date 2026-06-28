<?php
/**
 *
 *
 * @category     Shuxun Cms
 * @date         2015年10月3日
 * @author       Shuxun Team <kaiping.jiang@scshux.com>
 * @file         Tree.php
 */
namespace  ScshuxCms\Core;
class Tree
{

	private  $_data= array();

	private  $_idName = 'id';

	private  $_valueName = 'name';

	private  $_parentIdName = 'parent_id';

	private  $_picName = 'pic';

	/**
	 *
	 * @param 原始数据     $data
	 * @param string $idName
	 * @param string $valueName
	 * @param string $parentIdName
	 */
	public function __construct($data, $idName = 'id',$valueName = 'name',$parentIdName = 'parent_id',$picName='pic')
	{
		$data = arrayToObject($data);
		$this->_data = $data;
		$this->_idName = $idName;
		$this->_valueName = $valueName;
		$this->_parentIdName = $parentIdName;
		$this->_picName = $picName;
	}
	


	/**
	 * 獲取所有分類
	*/
	public  function getData()
	{
		return $this->_data;
	}

	/**
	 * 獲取分類options
	 * @param $id 選中的分類
	 */
	public  function getHtmlOptions($id = '',$outId = FALSE)
	{

		$catrgories = $this->unlimitedForLevel('&nbsp;&nbsp;&nbsp;');
		$htmlOptions = '';
        $currentkey = $this->_idName;
        $parentkey = $this->_parentIdName;
        $valname = $this->_valueName;
		foreach ($catrgories as $item)
		{
			if($item->{$currentkey} == $outId) continue;

			if($item->{$currentkey} == $id){
				$htmlOptions .= '<option value="'. $item->{$currentkey}.'" selected data-pid="'.$item->{$parentkey}.'">'. $item->delimiter. $item->{$valname}.'</option>';
			}else{
				$htmlOptions .= '<option value="'. $item->{$currentkey}.'" data-pid="'.$item->{$parentkey}.'">'. $item->delimiter. $item->{$valname}.'</option>';
			}
		}
		return $htmlOptions;
	}


	/**
	 * 一维数组
	 * @param $cate
	 * @param string $delimiter
	 * @param number $pid
	 * @param number $level
	 * @return Ambigous <multitype:, multitype:unknown >
	 */
	public function unlimitedForLevel ($delimiter = '———', $pid = 0, $level = 0)
	{
		$cate = $this->getData();
		$arr = array();
		$parentkey = $this->_parentIdName;
		$currentkey = $this->_idName;
		foreach ($cate as $v) {
			if ($v->$parentkey == $pid) {
				$v->level = $level + 1;
				$v->delimiter = str_repeat('&nbsp;&nbsp;', $level).str_repeat($delimiter, $level);
				$arr[] = $v;
				$arr = array_merge($arr,$this->unlimitedForLevel($delimiter, $v->{$currentkey},$v->level));
			}
		}

		return $arr;
	}

	/**
	 * 组成多维数组
	 * @param $cate
	 * @param string $name
	 * @param number $pid
	 * @return multitype:multitype:unknown
	 */
	public function unlimitedForLayer ( $name = 'child', $pid = 0,$domain='',$type='',$ident='')
	{
		$cate = $this->getData();
		$arr = array();
		$parentIdName = $this->_parentIdName;
		$idName = $this->_idName;
		$picName= $this->_picName;
		foreach ($cate as $v) {
			if ($v->{$parentIdName} == $pid) {

				$v->{$picName} = $v->{$picName}?$domain.$v->{$picName}:'';
				if($type != '') $v->type    = $type;
				if($ident != '') $v->ident  = $ident;
				$v->{$name} = $this->unlimitedForLayer($name, $v->{$idName},$domain,$type);

				$arr[] = $v;
			}
		}

		return $arr;
	}
	/**
	 * 组成一维数组
	 * @param string $field  需要返回的字段
	 * @param number $pid
	 * @param array $return 返回的数据
	 * @return multitype:multitype:unknown
	 */
	public function unlimitedForField ( $field = 'class_id', $pid = 0,$return=array())
	{
		$cate = $this->getData();
		$parentIdName = $this->_parentIdName;
		$idName = $this->_idName;
		foreach ($cate as $v) {
			if ($v->{$parentIdName} == $pid) {
				$return[] = $v->{$field};
				$childs   = $this->unlimitedForField($field, $v->{$idName},$return);

				$return   = array_merge($return,$childs);
			}
		}

		return array_unique($return);
	}
	/**
	 * 获取分类树
	 * @param string $name
	 * @param number $pid
	 * @return multitype:multitype:unknown
	 */
	public  function getTree($name = 'child', $pid = 0)
	{
		return  $this->unlimitedForLayer($name,$pid);
	}

	/**
	 * 传递一个子分类ID返回他的所有父级分类
	 *
	 * @param  $cate
	 * @param  $id
	 * @return Ambigous <multitype:, multitype:unknown >
	 */
	public function getParents ($cate, $id)
	{
		$arr = array();

		if (empty($cate)) {
			return $arr;
		}

		foreach ($cate as $v) {
			if ($v[$this->_idName] == $id) {
				$arr[] = $v;
				$arr = array_merge($this->getParents($cate, $v[$this->_parentIdName]), $arr);
			}
		}
		return $arr;
	}

	/**
	 * 传递一个子分类ID返回他的同级分类
	 *
	 * @param  $cate
	 * @param  $id
	 * @return multitype:|multitype:unknown
	 */
	public function getSameCate ($cate, $id)
	{
		$arr = array();
		$self = $this->getSelf($cate, $id);
		if (empty($self)) {
			return $arr;
		}

		foreach ($cate as $v) {
			if ($v[$this->_idName] == $self[$this->_parentIdName]) {
				$arr[] = $v;
			}
		}
		return $arr;
	}

	/**
	 * 判断分类是否有子分类,返回false,true
	 *
	 * @param array $cate
	 * @param unknown $id
	 * @return boolean
	 */
	public function hasChild ($cate, $id)
	{
		$arr = false;
		foreach ($cate as $v) {
			if ($v[$this->_parentIdName] == $id) {
				$arr = true;
				return $arr;
			}
		}

		return $arr;
	}

	/**
	 * 传递一个父级分类ID返回所有子分类ID
	 *
	 * @param $cate 全部分类数组
	 * @param $pid 父级ID
	 * @param $flag 是否包括父级自己的ID，默认不包括
	 *
	 */
	public function getChildsId ($pid, $flag = 0)
	{
		$cate = $this->getData();
		$arr = array();
		if ($flag) {
			$arr[] = $pid;
		}
		foreach ($cate as $v) {
			if ($v->{$this->_parentIdName} == $pid) {
				$arr[] = $v->{$this->_idName};
				$arr = array_merge($arr, $this->getChildsId($v->{$this->_idName}));
			}
		}

		return $arr;
	}



	/**
	 * 传递一个父级分类ID返回所有子级分类
	 *
	 * @param unknown $pid
	 * @return Ambigous <multitype:, multitype:unknown >
	 */
	public function getChilds ($pid)
	{
		$cate = $this->getData();
		$arr = array();
		foreach ($cate as $v) {
			if ($v->{$this->_parentIdName} == $pid) {
				$arr[] = $v->{$this->_idName};
				$arr = array_merge($arr, $this->getChilds($v->{$this->_idName}));
			}
		}
		return $arr;
	}

	/**
	 * 传递一个分类ID返回该分类相当信息
	 *
	 * @param integer $id
	 * @return Ambigous <multitype:, unknown>
	 */
	public function getSelf ($id)
	{
		$cate = $this->getData();
		$arr = array();
		foreach ($cate as $v) {
			if ($v->{$this->_idName} == $id) {
				$arr = $v;
				return $arr;
			}
		}
		return $arr;
	}

}