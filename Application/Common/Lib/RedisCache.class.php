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
    public function getPartResCache($suffix, $except_key=array()){
        $get_attrs = I('get.');
        $post_attrs = I('post.');
        $version = '';
        $version_pos = '';
        if (array_key_exists('version', $get_attrs)){
            $version = $get_attrs['version'];
            $version_pos = 'in_get';
        }
        if (array_key_exists('version', $post_attrs)){
            $version = $post_attrs['version'];
            $version_pos = 'in_post';
        }
        if (!$version){
            // version没有传递，则不需要版本处理，直接取缓存
            $key = $this->bornKey($except_key,$get_attrs, $post_attrs,$_SERVER['HTTP_HOST'],CONTROLLER_NAME, ACTION_NAME);
            return $this->get_cache_by_key($key, $suffix);
        }
        else{
            // 找到需要转换的version版本号
            $temp_version = $this->version_collection(ACTION_NAME, $version);
            if($temp_version)
                if($version_pos === 'in_post'){
                    $post_attrs['version'] = $temp_version;
                }
                else{
                    $get_attrs['version'] = $temp_version;
                }

            $key = $this->bornKey($except_key,$get_attrs, $post_attrs,$_SERVER['HTTP_HOST'],CONTROLLER_NAME, ACTION_NAME);
            return $this->get_cache_by_key($key, $suffix);
        }
    }

    private function get_cache_by_key($key, $suffix){
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

    private function set_cache_by_key($key, $value, $ttl, $suffix){
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

    /** 缓存数据，其key有当前访问uri及其参数决定。提供一个$suffix作为补充key
     * @param $suffix
     * @param $value
     * @param int $ttl
     * @param array 部分key将被剔除
     * @return bool
     */
    public function setPartResCache($suffix, $value, $ttl=60, $except_key=array()){
        $get_attrs = I('get.');
        $post_attrs = I('post.');
        $version = '';
        $version_pos = '';
        if (array_key_exists('version', $get_attrs)){
            $version = $get_attrs['version'];
            $version_pos = 'in_get';
        }
        if (array_key_exists('version', $post_attrs)){
            $version = $post_attrs['version'];
            $version_pos = 'in_post';
        }

        if (!$version){
            // version没有传递，则不需要版本处理，直接取缓存
            $key = $this->bornKey($except_key,$get_attrs, $post_attrs,$_SERVER['HTTP_HOST'],CONTROLLER_NAME, ACTION_NAME);
            return $this->set_cache_by_key($key, $value, $ttl, $suffix);
        }
        else{
            // 找到需要转换的version版本号
            $temp_version = $this->version_collection(ACTION_NAME, $version);
            if($temp_version)
                if($version_pos === 'in_post'){
                    $post_attrs['version'] = $temp_version;
                }
                else{
                    $get_attrs['version'] = $temp_version;
                }

            $key = $this->bornKey($except_key,$get_attrs, $post_attrs,$_SERVER['HTTP_HOST'],CONTROLLER_NAME, ACTION_NAME);
            return $this->set_cache_by_key($key, $value, $ttl, $suffix);
        }
    }

    // 排序规则， 生成一个controller的基础key，参数按照升序排列
    private function bornKey($except_key, $get_attrs, $post_attrs, $host, $controller_name, $action_name){
//        $get_attrs = I('get.');
//        $post_attrs = I('post.');

        if($host){
            // 去除主机名称中的www字符串
            $host = strpos($host, 'www')?(str_replace('www.','',$host)):($host);
        }

        $get_attr_keys = array_keys($get_attrs);
        $post_attr_keys = array_keys($post_attrs);

        $keys_array = array_merge($get_attr_keys, $post_attr_keys);
        $keys_array = $this->deleteKey($keys_array, $except_key);
        sort($keys_array);

        $get_post_attrs = array_merge($get_attrs, $post_attrs);

        $get_post_attrs_sorted = array();
        foreach($keys_array as $key){
            $temp = $key.':'.$get_post_attrs[$key];
            array_push($get_post_attrs_sorted, $temp);
        }

        $key_part2 = implode('+', $get_post_attrs_sorted);
        $key_part1 = $host.'+'.$controller_name.'+'.$action_name;

        $key = $key_part1.'$'.$key_part2;

        return $key;
    }

    // 去除一些url中的参数，使之不成为key的一部分，比如sessionId这个参数就不应该作为缓存key
    private function deleteKey($keys_array, $except_key){
        foreach($except_key as $except){
            $key_position = array_search($except, $keys_array);
            if($key_position !== false){
                unset($keys_array[$key_position]);
            }
        }
        return $keys_array;
    }

    private function version_collection($action_name, $version){
        // 某些action的缓存，多版本会共用一个缓存
        // 每个action可能对应多个版本号集合
        // 每个版本号集合的第一位代表该集合的转换值
        $version_same_kind = array('forumfilter'=> array(array('2.91', '2.91', '2.92')));

//        $actions = ['forumfilter'];
        if (!array_key_exists($action_name, $version_same_kind)){
            return null;
        }
        else{
            $versions_array = $version_same_kind[$action_name];
            foreach($versions_array as $versions ){
                foreach($versions as $temp_version){
                    if($temp_version === $version){
                        // 返回版本号转换值
                        return $versions[0];
                    }
                }
            }
            return null;
        }
    }
}