<?php
namespace Addons\Action\Controller;
use Admin\Controller\AddonsController;
class ActionController extends AddonsController{
	/* 添加积分规则 */
	public function add(){
		$this->meta_title = '添加积分规则';
		$current = U('/Admin/Addons/adminList/name/Action');
		$this->assign('current',$current);
		$this->display(T('Addons://Action@Action/edit'));
	}
	
	/* 编辑积分规则 */
	public function edit(){
		$this->meta_title = '修改积分规则';
		$id     =   I('get.id','');
		$current = U('/Admin/Addons/adminList/name/Action');
		$detail = D('Addons://Action/Action')->detail($id);
		$this->assign('data',$detail);
		$this->assign('current',$current);
		$this->display(T('Addons://Action@Action/edit'));
	}
	
	/* 禁用积分规则 */
	public function forbidden(){
		$this->meta_title = '禁用积分规则';
		$id     =   I('get.id','');
		if(D('Addons://Action/Action')->forbidden($id)){
			$this->success('成功禁用该积分规则', U('/admin/addons/adminlist/name/Action'));
		}else{
			$this->error(D('Addons://Action/Action')->getError());
		}
	}
	
	/* 启用积分规则*/
	public function off(){
		$this->meta_title = '启用积分规则';
		$id     =   I('get.id','');
		if(D('Addons://Action/Action')->off($id)){
			$this->success('成功启用该积分规则', U('/admin/addons/adminlist/name/Action'));
		}else{
			$this->error(D('Addons://Action/Action')->getError());
		}
	}	
	
	/* 删除积分规则 */
	public function del(){
		$this->meta_title = '删除积分规则';
		$id     =   I('get.id','');
		if(D('Addons://Action/Action')->del($id)){
			$this->success('删除成功', U('/admin/addons/adminlist/name/Action'));
		}else{
			$this->error(D('Addons://Action/Action')->getError());
		}
	}
	
	/* 更新积分规则 */
	public function update(){
		$this->meta_title = '更新积分规则';
		$rule = "table:member|field:score|condition:uid={\$self}|rule:score".$_POST['score']."|tox_money_rule:tox_money".$_POST['tox_money']."|tox_money_field:tox_money";
		if(!empty($_POST['cycle'])){$rule .= '|cycle:'.$_POST['cycle'];}//追加间隔
		if(!empty($_POST['max'])){$rule .= '|max:'.$_POST['max'];}//追加上限
		$_POST['rule'] = $rule;
		$res = D('Addons://Action/Action')->update();
		if(!$res){
			$this->error(D('Addons://Action/Action')->getError());
		}else{
			if($res['id']){
				$this->success('更新成功', U('/admin/addons/adminlist/name/Action'));
			}else{
				$this->success('新增成功', U('/admin/addons/adminlist/name/Action'));
			}
		}
	}
}
