<?php

namespace Addons\Favorite;

use Common\Controller\Addon;

/**
 * 签到插件
 * @author 嘉兴想天信息科技有限公司
 */
class FavoriteAddon extends Addon
{

    public $info = array(
        'name' => 'Favorite',
        'title' => '收藏',
        'description' => '收藏的功能',
        'status' => 1,
        'author' => 'RFly',
        'version' => '0.1'
    );


    public function install()
    {
        $db_prefix = C('DB_PREFIX');
        $sql = "
CREATE TABLE IF NOT EXISTS `{$db_prefix}favorite` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `appname` varchar(20) NOT NULL COMMENT '应用名',
  `row` int(11) NOT NULL COMMENT '应用标识',
  `uid` int(11) NOT NULL COMMENT '用户',
  `create_time` int(11) NOT NULL COMMENT '发布时间',
  `table` varchar(20) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='支持的表'  ;
        ";
        $rs = D('')->execute($sql);
        return true;
    }

    public function uninstall()
    {
        return true;
    }

    //实现的钩子方法
    public function favorite($param)
    {

       $param['jump']=isset($param['jump'])?$param['jump']:'';
        $this->assign($param);

        $map_Favorite['appname'] = $param['app'];
        $map_Favorite['table'] = $param['table'];
        $map_Favorite['row'] = $param['row'];

        $count = $this->getFavoriteCountCache($map_Favorite);

        $map_favorite = array_merge($map_Favorite, array('uid' => is_login()));
        $favoriteed = D('Favorite')->where($map_favorite)->count();


        $this->assign('count', $count);
        $this->assign('favoriteed', $favoriteed);
        $this->display('favorite');

    }

    /**
     * @param $map_favorite
     * @return mixed
     * @auth 陈一枭
     */
    private function getFavoriteCountCache($map_favorite)
    {
        $cache_key = "favorite_count_" . implode('_', $map_favorite);
        $count = S($cache_key);
        if (empty($count)) {
            $count = D('Favorite')->where($map_favorite)->count();
            S($cache_key, $count);
            return $count;
        }
        return $count;
    }


}








