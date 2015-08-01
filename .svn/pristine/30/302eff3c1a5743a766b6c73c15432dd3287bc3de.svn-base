<?php
namespace Addons\Action\Model;
use Think\Model;

/**
 * 分类模型
 */
class ActionModel extends Model{
	
	/* 自动完成规则 */
	protected $_auto = array(
			array('create_time', 'getCreateTime', self::MODEL_BOTH,'callback'),
	);
	
	
	protected function _after_find(&$result,$options) {
		$rules = explode(';', $result['rule']);
		$return = array();
		foreach ($rules as $key => &$rule) {
			$rule = explode('|', $rule);
			foreach ($rule as $k => $fields) {
				$field = empty($fields) ? array() : explode(':', $fields);
				if(!empty($field)){$return[$field[0]] = $field[1];}
				if($field[0] == 'rule') {$return[$field[0]] = substr($field[1],5);}
				if($field[0] == 'tox_money_rule'){$return[$field[0]] = substr($field[1],9);}
			}
			//cycle(检查周期)和max(周期内最大执行次数)必须同时存在，否则去掉这两个条件
			if (!array_key_exists('cycle', $return) || !array_key_exists('max', $return)) {
				unset($return['cycle'], $return[$key]);
			}
		}
		$result['score'] = empty($return['rule']) ? 0 : $return['rule'];
		$result['tox_money'] = empty($return['tox_money_rule']) ? 0 : $return['tox_money_rule'];
		$result['cycle'] = empty($return['cycle']) ? 0 : $return['cycle'];
		$result['max'] = empty($return['max']) ? 0 : $return['max'];
		$result['cycletext'] = empty($return['cycle']) ? '没有时间限制' : '间隔'.$return['cycle'].'小时';
		$result['maxtext'] = empty($return['max']) ? '每天0次' : '每天'.$return['max'].'次';
		$result['statustest'] = $result['status'] == 1 ? '正常' : '禁用';
		$result['update_time'] = date('Y-m-d H:i:s', $result['update_time']);
	}
	
	protected function _after_select(&$result,$options){
		foreach($result as &$record){
			$this->_after_find($record,$options);
		}
	}
	
	
	/* 获取编辑数据 */
	public function detail($id){
		$data = $this->find($id);
		return $data;
	}
	
	/* 禁用 */
	public function forbidden($id){
		return $this->save(array('id'=>$id,'status'=>'0'));
	}
	
	/* 启用 */
	public function off($id){
		return $this->save(array('id'=>$id,'status'=>'1'));
	}
	
	/* 删除 */
	public function del($id){
		return $this->delete($id);
	}
	
	/**
	 * 新增或更新一个文档
	 * @return boolean fasle 失败 ， int  成功 返回完整的数据
	 * @author huajie <banhuajie@163.com>
	 */
	public function update(){
		/* 获取数据对象 */
		$data = $this->create();
		if(empty($data)){
			return false;
		}
		/* 添加或新增基础内容 */
		if(empty($data['id'])){ //新增数据
			$id = $this->add(); //添加基础内容
			if(!$id){
				$this->error = '新增日志内容出错！';
				return false;
			}
		} else { //更新数据
			$status = $this->save(); //更新基础内容
			if(false === $status){
				$this->error = '更新日志内容出错！';
				return false;
			}
		}
	
		//内容添加或更新完成
		return $data;
	
	}	
	
	/* 时间处理规则 */
	protected function getCreateTime(){
		$create_time    =   I('post.create_time');
		return $create_time?strtotime($create_time):NOW_TIME;
	}
	
}