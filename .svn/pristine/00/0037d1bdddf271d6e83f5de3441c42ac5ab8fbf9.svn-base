<?php

namespace Addons\OTcaiji;
use Common\Controller\Addon;

/**
 * OT采集插件
 * @author Marvin(柳英伟)
 */

    class OTcaijiAddon extends Addon{

        public $info = array(
            'name'=>'OTcaiji',
            'title'=>'OT采集',
            'description'=>'OT采集插件',
            'status'=>1,
            'author'=>'Marvin(柳英伟)',
            'version'=>'0.2'
        );

        public $admin_list = array(
            'model'=>'listtmp',		//要查的表
			'fields'=>'*',			//要查的字段
			'map'=>'',				//查询条件, 如果需要可以再插件类的构造方法里动态重置这个属性
			'order'=>'id desc',		//排序,
			'listKey'=>array( 		//这里定义的是除了id序号外的表格里字段显示的表头名
				'title'=>'标题',
				'url'=>'URL',
				'dates'=>'采集时间'
			),
        );
        Public function cateList(){
			$list= M('Category')->where(array('status' => 1))->field('id,title,pid,allow_publish')->order('pid,sort')->select();
			var_dump($list);
			$this->assign('cate',$list);
			$this->display('caiji');
		}
        public $custom_adminlist = "caiji.html";

        public function install(){
			//创建数据表		
$sql2=<<<SQL
CREATE TABLE IF NOT EXISTS `{$this->db_prefix()}listTmp` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(200) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `url` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `source` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `sourceurl` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `dates` int(10) NOT NULL,
  `st` int(1) NOT NULL,
  `zt` int(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=121 ;
SQL;
		D()->execute($sql2);
                //写入应用配置文件
$crons=<<<CRON
<?php
return array(
    'caiji' => array('caiji',30),
);
CRON;
$tags=<<<TAGE
<?php
return array(
	'app_init'=>array('Common\Behavior\InitHook')
);
TAGE;
$caiji=<<<CAIJI
<?php 
R('Addons://OTcaiji/OTcaiji/caji');
?>
CAIJI;
                  if(!IS_WRITE){
                          return '由于您的环境不可写，请复制下面的配置文件内容覆盖到相关的配置文件，然后再登录后台。<p>./Application/Common/Conf/crons.php</p>
                          <textarea name="" style="width:650px;height:185px">'.$crons.'</textarea>
                          <p>./Application/Common/Conf/tags.php</p>
                          <textarea name="" style="width:650px;height:125px">'.$tags.'</textarea>';
                  }else{
                        file_put_contents(APP_PATH . '/Common/Conf/crons.php', $crons);
                        file_put_contents(APP_PATH . '/Common/Conf/tages.php', $tags); 
                        //copy("./cron/CronRunBehavior.class.php","./ThinkPHP/Library/Behavior/CronRunBehavior.class.php");
                        if(mkdir ( APP_PATH . '/Common/cron/' ,  0700 )){
                            file_put_contents(APP_PATH . '/Common/cron/caiji.php', $caiji); 
                        }     
                  }
                  
            //添加钩子
			$Hooks = M("Hooks");
			$OTcaiji = array(array(
				'name' => 'OTcaiji',
				'description' => 'OT采集插件钩子',
				'type' => 1,
				'update_time' => NOW_TIME,
				'addons' => 'OTcaiji'
			));
			$Hooks->addAll($OTcaiji,array(),true);
			if ( $Hooks->getDbError() ) {
				session('addons_install_error',$Hooks->getError());
				return false;
			}
            return true;
        }

        public function uninstall(){
            $Hooks = M("Hooks");
			$map['name']  = array('in','OTcaiji');
			$res = $Hooks->where($map)->delete();
			if($res == false){
				session('addons_install_error',$Hooks->getError());
				return false;
			}
            return true;
        }
		//获取表前缀
		public function db_prefix(){
			$db_prefix = C('DB_PREFIX');
			return $db_prefix;
		}
        //实现的OTcaiji钩子方法
        public function OTcaiji($param){
			
        }

    }