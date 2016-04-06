<?php

/**
 * Created by PhpStorm.
 * User: Whispers
 * Date: 2016/4/5
 * Time: 12:31
 * 用于缓存Api的结果
 */

//
class RedisCache
{
    private $cache;

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

    /** 获取当前request的缓存结果
     * @return mixed|null
     */
    public function getResCache(){
        $q = $_SERVER['REQUEST_URI'];
        $has = $this->cache->exists($q);
        if($has){
            $value = $this->cache->get($q);
            $arr = json_decode($value);
            return $arr;
        }
        else{
         return null;
        }
    }

    public function close(){
        $this->cache->close();
    }

    /** 设置api结果缓存
     * @param $message 提示消息
     * @param $array 返回数组
     * @param int $ttl 过期时间
     * @return bool 是否成功
     */
    public function setResCache($message, $array, $ttl=60){
        $arr = array();
        $arr['msg'] = $message;
        $arr['content'] = $array;
        $str = json_encode($arr);
        $success = $this->cache->setex($_SERVER['REQUEST_URI'], $ttl, $str);
        return $success;
    }
}