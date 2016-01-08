<?php
/**
 * Created by PhpStorm.
 * Author: RFly
 * Date: 2/4/15
 * Time: 4:20 PM
 * =======================================================
 * 扫描过程：
 * 第一步：浏览器请求initdata，生成一个唯一的guid以及其他参数(/qrscan/initdata)
 * 第二步：浏览器根据第一步返回的参数身材二维码，
 * 第三步：浏览器启动定时器，带上guid参数不停的询问服务器（askstatus），这个guid处理的咋样了(/qrscan/askstatus/guid/*****)
 * 第四步：移动端扫描二维码，获取guid以及其他参数
 * 第五步：移动端请求服务器scan函数，进行处理(/scan/guid/****)(注意：这一步必须要登陆了的用户才能处理成功)
 * 第六步：浏览器通过askstatus 获取到成功的提示，表示后台已经处理了此二维码
 *=========================================================
 * 二维码扫描记录表(qr_scan)中的status状态可能的值有
 * -2 扫描了，但已经使用此记录登陆果了
 * -1 过期了
 * 1 有效，等待扫描
 * 2 有效，已经扫描了，还未请求
 * =========================================================
 * 服务器端如何判断是否扫描过了，何人扫描的
 * session中 ('is_scanned',1);标记已经扫描过了
 × session ('scan_uid'); 记录的是扫描人的uid
 */

namespace App\Controller;

use Think\Controller;

class QrscanController extends AppController
{


    public function _initialize()
    {

    }

    /**
     * 请求生成二维码需要的数据
     */
    public function initdata()
    {
        $mode = M('qr_scan');

        if($mode)
        {
            $row = array(
                'guid' => $this->gen_guid(),
                'expire' => time() + 60*3,
                'status' => 1,
            );

            $mode->create($row);
            $mode->add();

            $this->apiSuccess($row['guid']);
        }

        $this->apiError(1,'');
    }

    /**
     * 询问guid的状态
     * 一个处理过的guid，经过此函数后，就自动无效，一次性的
     * @param $guid
     *
     *
     */
    public function askstatus($guid)
    {
        $map = array(
            'guid' => $guid
        );

        $modal = M('qr_scan');

        $data = $modal->where($map)->find();

        if(empty($data)){
            $this->apiError(-1,"无效");
        }

        if($data['status'] == '1')
        {
            //status为1，表示任然有效

            if($data['expire'] < time())
            {
                //已经过期,标记为无效状态

                $this->modify_status($modal,$guid,-1);

                $this->apiError(-1,"无效");

            }else{

                //此记录有效，并且还未扫描
                $this->apiError(0,"未处理");
            }

        }elseif($data['status'] == 2){

            //有效，并且已经扫描过了

            if($data['expire'] < time())
            {
                //扫描过了，但是长时间没用，过期了
                $this->modify_status($modal,$guid,-1);

                $this->apiError(-1,"无效");
            }else{

                //把状态修改为已使用
                $this->modify_status($modal,$guid,-2);

                //在session中记录扫描相关信息
                session('is_scanned',1);
                session('scan_uid',$data['scan_uid']);
                $_SESSION["token"]=$guid;

                $type = $data['type'];
                $category_id = $data['category_id'];

                if($type==1){  // 单个作业
                    //$extra['url'] = 'http://hisihi.com/hiworks_list.php/file/download/id/'.$category_id;
                    $extra['url'] = C('HOST_NAME_PREFIX').'hiworks_list.php/index/index/download/'.$category_id;
                } else if($type==0){  // 作业分类
                    if($category_id==0){ // 全部作业
                        $extra['url'] = C('HOST_NAME_PREFIX').'hiworks_list.php/index/index';
                    } else {
                        $extra['url'] = C('HOST_NAME_PREFIX').'hiworks_list.php/index/index/cate/'.$category_id.'.html';
                    }
                }
                $this->apiSuccess('', null, $extra);
            }

        }else{

            //没有对应的guid，或是已经无效了
            $this->apiError(-1,"无效");
        }

    }

    /**
     * 扫描处理
     * @param $guid
     * @param int $type  0 作业列表 1 单个作业
     * @param int $category_id 作业分类id,如果是单个作业就是作业id
     */
    public function scan($guid, $type=0, $category_id=0)
    {
        $uid = get_uid();

        if(empty($uid))
        {
            //未登陆，不能处理扫描
            $this->apiError('-2','未登录');
        }

        $map = array(
            'guid' => $guid,
            'status' => 1
        );

        $modal = M('qr_scan');
        $data = $modal->where($map)->find();

        if(empty($data) || $data['expire'] < time())
        {
            //没有找到状态为待扫描的记录，或是找到了，但它已经过期了
            $this->apiError(-1,'无效或过期');
        }

        $row = array(
            'status'      => 2,
            'scan_time'   => time(),
            'scan_uid'    => $uid,
            'scan_session'=> session_id(),
            'type'        => $type,
            'category_id' => $category_id,
        );

        //修改状态，并记录扫描时间和扫描人的uid
        $modal->where($map)->save($row);

        //在session中记录扫描相关信息
        session('is_scanned',1);
        session('scan_uid',$uid);

        $this->apiSuccess('');
    }

    private function gen_guid()
    {
        $GUID = strtoupper(md5(uniqid(mt_rand(), true)));

        return $GUID;
    }

    /**
     * 修改状态
     * @param $modal
     * @param $guid
     * @param $status
     */
    private function modify_status(&$modal,$guid,$status){

        $map = array(
            'guid' => $guid
        );

        $row = array(
            'status' => $status,
        );

        $modal->where($map)->save($row);
    }
}