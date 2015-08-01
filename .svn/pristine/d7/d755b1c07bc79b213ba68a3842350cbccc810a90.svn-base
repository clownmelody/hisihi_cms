<?php
namespace Addons\Action;
use Common\Controller\Addon;
use Think\Db;

/**
 * 积分规则
 * @author quick
 */
    class ActionAddon extends Addon{
        public $info = array(
            'name'=>'Action',
            'title'=>'积分规则',
            'description'=>'积分规则设置，金币规则设置',
            'status'=>1,
            'author'=>'quick(onep2p.com)',
            'version'=>'0.1'
        );
		public $addon_path = './Addons/Action/';
        public $admin_list = array(
            'listKey' => array(
        				'name'=>'行为标识',
        				'title'=>'行为说明',
        				'remark'=>'行为描述',
            			'score'=>'积分变化',
            			'tox_money'=>'金币变化',
            			'cycletext'=>'变化周期',
            			'maxtext'=>'每天上限',
            			'statustest'=>'行为状态',
        				'update_time'=>'更新时间',
        		),
        		'model'=>'Action',
        		'where'=>'type = 1',
        		'order'=>'id asc'
        );
        public $custom_adminlist = 'adminlist.html';
        public function install(){
            return true;
        }
        public function uninstall(){
            return true;
        }

        //实现的pageFooter钩子方法
        public function AdminIndex($param){

        }     
    }