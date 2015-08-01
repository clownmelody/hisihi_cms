<?php
/**
 * Created by PhpStorm.
 * User: Alan
 * Date: 14-3-19
 * Time: 下午2:19
 */
namespace Addons\Favorite\Controller;

use Home\Controller\AddonsController;

class FavoriteController extends AddonsController
{


    /*
     *收藏
     */

    public function doFavorite()
    {
        if (!is_login()) {
            exit(json_encode(array('status' => 0, 'info' => '请登陆后再收藏。')));
        }
        $appname = I('POST.appname');
        $table = I('POST.table');
        $row = I('POST.row');
        $message_uid = intval(I('POST.uid'));
        $favorite['appname'] = $appname;
        $favorite['table'] = $table;
        $favorite['row'] = $row;
        $favorite['uid'] = is_login();

        if (D('Favorite')->where($favorite)->count()) {

            exit(json_encode(array('status' => 0, 'info' => '您已经收藏过，不能再收藏了。')));
        } else {
            $favorite['create_time'] = time();
            if (D('Favorite')->where($favorite)->add($favorite)) {

                $this->clearCache($favorite);

                $user = query_user(array('username'));
                if (I('POST.jump') == 'no') {
                    $jump = $_SERVER['HTTP_REFERER']; //如果设置了jump=no，则默认使用引用页
                } else {
                    $jump = U($appname . '/Index/' . $table . 'Detail', array('id' => $row));//否则按照约定规则组合消息跳转页面。
                }
                D('Message')->sendMessage($message_uid, $user['username'] . '给您点了个赞。', $title =$user['username'] . '赞了您。', $jump, is_login());
                exit(json_encode(array('status' => 1, 'info' => '感谢您的支持。')));
            } else {
                exit(json_encode(array('status' => 0, 'info' => '写入数据库失败。')));
            }

        }
    }

    /**
     * @param $favorite
     * @auth RFly
     */
    private function clearCache($favorite)
    {
        unset($favorite['uid']);
        unset($favorite['create_time']);
        $cache_key = "favorite_count_" . implode('_', $favorite);
        S($cache_key, null);
    }
}

