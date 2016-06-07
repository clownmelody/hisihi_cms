<?php

/**
 * Created by PhpStorm.
 * User: Whispers
 * Date: 2016/4/9
 * Time: 14:25
 */
require_once('Application/Common/Lib/RedisCache.class.php');

class ForumFilterCache extends \RedisCache
{
    private $public_suffix_post = 'post_list_no_supported';
    private $public_suffix_total_count = 'total_count';
    private $cache_time = 60;
    private $public_except_key = array('session_id');


    public function setPublicResCache($post_list, $total_count){
        foreach($post_list as $key => $f){
            unset($f['isSupportd']);
            $post_list[$key] = $f;
        }
        parent::setPartResCache($this->public_suffix_post, $post_list,
            $this->cache_time, $this->public_except_key);

        parent::setPartResCache($this->public_suffix_total_count, $total_count,
            $this->cache_time, $this->public_except_key);
    }

    public function getPublicResCache(){
        $post_list = parent::getPartResCache($this->public_suffix_post, $this->public_except_key);
        if($post_list==null) {
            return null;
        }
        $total_count = parent::getPartResCache($this->public_suffix_total_count, $this->public_except_key);
        if($total_count==null){
            return null;
        }
        // ���ӵĵ�����Ϣ�͸����û���أ��޷���Ϊ���湫�ã�������Ҫ�ٴβ�ѯ
        $this->fillWithIfSupported($post_list);
        return array('list'=>$post_list, 'total_count'=>intval($total_count));
    }

    // �򻺴������ͬ�û���صĵ�����Ϣ��������Ϊ������ؿͻ���
    private function fillWithIfSupported($cached){
        $forum_list = $cached;
        foreach($forum_list as $f){
            $post_id = $f->post_id;
            $map_support['row'] = $post_id;
            $map_supported = array_merge($map_support, array('uid' => is_login()));
            $supported = M('Support')->where($map_supported)->count();
            $f->isSupportd = $supported;
        }
    }
}