<?php
/**
 * Created by PhpStorm.
 * User: yangchujie
 * Date: 16/8/25
 * Time: 14:32
 * Desc: 用于缓存论坛各 Tab 的帖子 id 列表
 */

class ForumPostCache {
    private $cache;
    const KEY_PREFIX = 'PHP_FORUM_ID_LIST';
    const RECOMMEND_TAB_ID_LIST_KEY = 'recommend_tab';
    const LATEST_TAB_ID_LIST_KEY = 'latest_tab_';

    function __construct(){
        $redis = new Redis();
        $redis_host = C('REDIS_HOST');
        $redis_port = C('REDIS_PORT');
        $redis_auth = C('REDIS_AUTH');
        $redis_db_num = C('REDIS_DB_NUM');
        $redis->connect($redis_host, $redis_port);
        $redis->auth($redis_auth);
        $redis->select($redis_db_num);
        $this->cache = $redis;
    }

    /**
     * 添加新帖 id 到 推荐 Tab 列表中
     * @param $post_id
     */
    public function addNewPostToRecommendTabList($post_id){

    }

    /**
     * 从 推荐 Tab 列表中，删除帖子 id
     * @param $post_id
     */
    public function removePostInRecommendTabList($post_id){

    }

    /**
     * 分页获取推荐 Tab 的帖子 id 列表
     * @param $page
     * @param $count
     */
    public function getPostsInRecommendTabByPage($page, $count){

    }

    /**
     * 添加新帖 id 到指定用户的 最新 Tab 列表中
     * @param $uid
     * @param $post_id
     */
    public function addNewPostToUserLatestTabList($uid, $post_id){

    }

    /**
     * 从用户的 最新 Tab 列表中移除帖子 id
     * @param $uid
     * @param $post_id
     */
    public function removePostInUserLatestTabList($uid, $post_id){

    }

    /**
     * 分页获取用户 最新 Tab 列表中的帖子 id 列表
     * @param $page
     * @param $count
     */
    public function getPostsInUserLatestTabByPage($page, $count){

    }



}
