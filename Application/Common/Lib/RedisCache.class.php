<?php

/**
 * Created by PhpStorm.
 * User: Whispers
 * Date: 2016/4/5
 * Time: 12:31
 * 用于缓存Api的结果
 */


class RedisCache
{
    private $cache;
    private $full_url;
    const prefix = 'hisihi_php';

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

        $this->full_url = $_SERVER['HTTP_HOST'].':'.$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
    }

    /** 获取当前request的缓存结果
     * @return mixed|null
     */
    public function getResCache($controller){
//        $q = $_SERVER['REQUEST_URI'];
        $has = $this->cache->exists($this->full_url);
        if($has){
            $value = $this->cache->get($this->full_url);
            $arr = json_decode($value);
            $msg = $arr->msg;
            $arr = $arr->content;
            $controller->apiSuccess($msg, null, $arr);
        }
        else{
         return null;
        }
    }

    public function close(){
        $this->cache->close();
    }

    /** 缓存整个action的返回结果，缓存后会中断后续执行，将缓存结果返回客户端.仅支持数组缓存
     * @param $controller 被缓存的控制器实例
     * @param $message 提示消息
     * @param $array 返回数组
     * @param int $ttl 过期时间
     * @return bool 是否成功
     *
     */
    public function setResCache($controller, $message, $array, $ttl=60){
        $arr = array();
        $arr['msg'] = $message;
        $arr['content'] = $array;
        $str = json_encode($arr);

        $success = $this->cache->setex($this->full_url, $ttl, $str);
        $controller->apiSuccess($message, null, $array);
    }

    /** 获取缓存数据，其key有当前访问uri及其参数决定。
     *
     */
    public function getPartResCache($suffix){
        $key = $this->bornKey();
        $key = $key.'$'.$suffix;
        $has = $this->cache->exists($key);

        if($has){
            $value = $this->cache->get($key);
            $json = json_decode($value);
            if($json) {
                return $json;
            }
            else{
                // 不是json对象，可能是字符串
                return $value;
            }
        }
        else{
            return null;
        }
    }

    /** 缓存数据，其key有当前访问uri及其参数决定。提供一个$suffix作为补充key
     * @param $suffix
     * @param $value
     * @param int $ttl
     * @return bool
     */
    public function setPartResCache($suffix, $value, $ttl=60){
        $key = $this->bornKey();
        $key = $key.'$'.$suffix;

        $value_type = gettype($value);
        switch ($value_type){
            case 'string':
                break;
            case 'array':
                $value = json_encode($value);
        }
        $success = $this->cache->setex($key, $ttl, $value);
        return $success;
    }

    // 排序规则， 生成一个controller的基础key，参数按照升序排列
    private function bornKey(){
        $get_attrs = I('get.');
        $post_attrs = I('post.');

        $get_attr_keys = array_keys($get_attrs);
        $post_attr_keys = array_keys($post_attrs);

        $keys_array = array_merge($get_attr_keys, $post_attr_keys);
        sort($keys_array);

        $get_post_attrs = array_merge($get_attrs, $post_attrs);

        $get_post_attrs_sorted = array();
        foreach($keys_array as $key){
            $temp = $key.':'.$get_post_attrs[$key];
            array_push($get_post_attrs_sorted, $temp);
        }

        $key_part2 = implode('+', $get_post_attrs_sorted);
        $key_part1 = $_SERVER['HTTP_HOST'].'+'.CONTROLLER_NAME.'+'.ACTION_NAME;

        $key = $key_part1.'$'.$key_part2;

        return $key;
    }
}