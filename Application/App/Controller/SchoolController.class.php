<?php
/**
 * Created by PhpStorm.
 * Author: RFly
 * Date: 2/4/15
 * Time: 4:20 PM
 * =======================================================
 */

namespace App\Controller;

use Think\Controller;

class SchoolController extends AppController
{
    private $pagesize = 10;//每页记录数

    public function _initialize()
    {

    }

    /**
     * 获取所有省的信息
     */
    public  function  province(){
        $list = json_decode('[{"province_id":"1","province_name":"\u5317\u4eac"},{"province_id":"2","province_name":"\u5929\u6d25"},{"province_id":"3","province_name":"\u6cb3\u5317"},{"province_id":"4","province_name":"\u5c71\u897f"},{"province_id":"5","province_name":"\u5185\u8499\u53e4"},{"province_id":"6","province_name":"\u8fbd\u5b81"},{"province_id":"7","province_name":"\u5409\u6797"},{"province_id":"8","province_name":"\u9ed1\u9f99\u6c5f"},{"province_id":"9","province_name":"\u4e0a\u6d77"},{"province_id":"10","province_name":"\u6c5f\u82cf"},{"province_id":"11","province_name":"\u6d59\u6c5f"},{"province_id":"12","province_name":"\u5b89\u5fbd"},{"province_id":"13","province_name":"\u798f\u5efa"},{"province_id":"14","province_name":"\u6c5f\u897f"},{"province_id":"15","province_name":"\u5c71\u4e1c"},{"province_id":"16","province_name":"\u6cb3\u5357"},{"province_id":"17","province_name":"\u6e56\u5317"},{"province_id":"18","province_name":"\u6e56\u5357"},{"province_id":"19","province_name":"\u5e7f\u4e1c"},{"province_id":"20","province_name":"\u5e7f\u897f"},{"province_id":"21","province_name":"\u6d77\u5357"},{"province_id":"22","province_name":"\u91cd\u5e86"},{"province_id":"23","province_name":"\u56db\u5ddd"},{"province_id":"24","province_name":"\u8d35\u5dde"},{"province_id":"25","province_name":"\u4e91\u5357"},{"province_id":"26","province_name":"\u897f\u85cf"},{"province_id":"27","province_name":"\u9655\u897f"},{"province_id":"28","province_name":"\u7518\u8083"},{"province_id":"29","province_name":"\u9752\u6d77"},{"province_id":"30","province_name":"\u5b81\u590f"},{"province_id":"31","province_name":"\u65b0\u7586"},{"province_id":"32","province_name":"\u53f0\u6e7e"},{"province_id":"33","province_name":"\u9999\u6e2f"},{"province_id":"34","province_name":"\u6fb3\u95e8"}]');
        if($list){
            $resultlist = array();
            $resultlist["data"] = $list;
            $this->apiSuccess("获取省信息成功", null, $resultlist);
        }else{
            $this->apiError("未查询到省信息");
        }
    }

    /**
     * 根据省id查询所有高校信息
     * @param int $provinceid   省id
     */
    public function school($provinceid = 1){
        //实例化空模型
        //或者使用M快捷方法是等效的
        $Model = M();
        // 计算总数
        $countstr = "SELECT count(*) as count FROM hisihi_school where school_pro_id = ".$provinceid;
        $count = $Model->query($countstr);
        $total = $count[0]['count'];

        $sqlstr = "SELECT school_id, school_name, school_pro_id, school_schooltype_id FROM hisihi_school where school_pro_id = ".$provinceid;
        //进行原生的SQL查询
        $list = $Model->query($sqlstr);
        if($total == 0){
            $resultlist = array();
            $resultlist["totalcount"] = $total;
            $resultlist["data"] = array();
            $this->apiSuccess("获取高校信息成功", null, $resultlist);
        }else
        {
            if($list){
                $resultlist = array();
                $resultlist["totalcount"] = $total;
                $resultlist["data"] = $list;
                $this->apiSuccess("获取高校信息成功", null, $resultlist);
            }else{
                $this->apiError("未查询到高校信息");
            }
        }
    }

    public function hot_school(){
        //实例化空模型
        //或者使用M快捷方法是等效的
        $Model = M();
        // 计算总数
        $countstr = "SELECT count(*) as count FROM hisihi_school where is_hot = 1";
        $count = $Model->query($countstr);
        $total = $count[0]['count'];

        $sqlstr = "SELECT school_id, school_name, school_pro_id, school_schooltype_id FROM hisihi_school where is_hot = 1";
        //进行原生的SQL查询
        $list = $Model->query($sqlstr);
        if($total == 0){
            $resultlist = array();
            $resultlist["totalcount"] = $total;
            $resultlist["data"] = array();
            $this->apiSuccess("获取热门高校信息成功", null, $resultlist);
        } else {
            if($list){
                $resultlist = array();
                $resultlist["totalcount"] = $total;
                $resultlist["data"] = $list;
                $this->apiSuccess("获取热门高校信息成功", null, $resultlist);
            } else {
                $this->apiError("未查询到高校信息");
            }
        }
    }

    /**
     * 根据省id分页查询高校信息
     * @param int $provinceid   省id
     * @param int $curpage      当前页码
     */
    public  function  schoolpaging($provinceid = 1,$curpage=1){
        //实例化空模型
        //或者使用M快捷方法是等效的
        $Model = M();
        // 计算总数
        $countstr = "SELECT count(*) as count FROM hisihi_school where school_pro_id = ".$provinceid;
        $count = $Model->query($countstr);
        $total = $count[0]['count'];
        $totalpage = intval($total / $this->pagesize);//取整
        $page1 = $total % $this->pagesize;
        if($page1 > 0)
            $totalpage += 1;
        if($curpage > $totalpage)
            $curpage = $totalpage;
        if($curpage < 1)
            $curpage = 1;
        $limitindex = ($curpage-1)*$this->pagesize;

        $sqlstr = "SELECT * FROM hisihi_school where school_pro_id = ".$provinceid." limit ".$limitindex.",".$this->pagesize;
        //进行原生的SQL查询
        $list = $Model->query($sqlstr);
        if($list){
            $resultlist = array();
            $resultlist["totalcount"] = $total;
            $resultlist["totalpage"] = $totalpage;
            $resultlist["curpage"] = $curpage;
            $resultlist["pagesize"] = $this->pagesize;
            $resultlist["data"] = $list;
            $this->apiSuccess("获取高校信息成功", null, $resultlist);
        }else{
            $this->apiError("未查询到高校信息");
        }
    }

    /**
     * 查询所有高校信息
     */
    public  function  allschool(){
        //实例化空模型
        //或者使用M快捷方法是等效的
        $Model = M();
        // 计算总数
        $countstr = "SELECT count(*) as count FROM hisihi_school";
        $count = $Model->query($countstr);
        $total = $count[0]['count'];

        $sqlstr = "SELECT * FROM hisihi_school";
        //进行原生的SQL查询
        $list = $Model->query($sqlstr);
        if($list){
            $resultlist = array();
            $resultlist["totalcount"] = $total;
            $resultlist["data"] = $list;
            $this->apiSuccess("获取高校信息成功", null, $resultlist);
        }else{
            $this->apiError("未查询到高校信息");
        }
    }

    /**
     * 分页查询所有高校信息
     * @param int $curpage  当前页码
     */
    public  function  allschoolpaging($curpage=1){
        //实例化空模型
        //或者使用M快捷方法是等效的
        $Model = M();
        // 计算总数
        $countstr = "SELECT count(*) as count FROM hisihi_school";
        $count = $Model->query($countstr);
        $total = $count[0]['count'];
        $totalpage = intval($total / $this->pagesize);//取整
        $page1 = $total % $this->pagesize;
        if($page1 > 0)
            $totalpage += 1;
        if($curpage > $totalpage)
            $curpage = $totalpage;
        if($curpage < 1)
            $curpage = 1;
        $limitindex = ($curpage-1)*$this->pagesize;

        $sqlstr = "SELECT * FROM hisihi_school"." limit ".$limitindex.",".$this->pagesize;
        //进行原生的SQL查询
        $list = $Model->query($sqlstr);
        if($list){
            $resultlist = array();
            $resultlist["totalcount"] = $total;
            $resultlist["totalpage"] = $totalpage;
            $resultlist["curpage"] = $curpage;
            $resultlist["pagesize"] = $this->pagesize;
            $resultlist["data"] = $list;
            $this->apiSuccess("获取高校信息成功", null, $resultlist);
        }else{
            $this->apiError("未查询到高校信息");
        }
    }

    /**
     * 获取高校类型信息
     */
    public  function  schooltypes(){
        //实例化空模型
        //或者使用M快捷方法是等效的
        $Model = M();
        //进行原生的SQL查询
        $list = $Model->query('SELECT * FROM hisihi_school_type');
        if($list){
            $resultlist = array();
            $resultlist["data"] = $list;
            $this->apiSuccess("获取高校类型信息成功", null, $resultlist);
        }else{
            $this->apiError("未查询到高校类型信息");
        }
    }

    /**根据类型id查询所有高校信息
     * @param int $typeid
     */
    public  function  schooltype($typeid = 1){
        //实例化空模型
        //或者使用M快捷方法是等效的
        $Model = M();
        // 计算总数
        $countstr = "SELECT count(*) as count FROM hisihi_school where school_schooltype_id = ".$typeid;
        $count = $Model->query($countstr);
        $total = $count[0]['count'];

        $sqlstr = "SELECT * FROM hisihi_school where school_schooltype_id = ".$typeid;
        //进行原生的SQL查询
        $list = $Model->query($sqlstr);
        if($list){
            $resultlist = array();
            $resultlist["totalcount"] = $total;
            $resultlist["data"] = $list;
            $this->apiSuccess("获取高校信息成功", null, $resultlist);
        }else{
            $this->apiError("未查询到高校信息");
        }
    }

    /**根据高校类型分页查询高校信息
     * @param int $type     高校类型
     * @param int $curpage  当前页码
     */
    public  function  schooltypepaging($typeid = 1,$curpage=1){
        //实例化空模型
        //或者使用M快捷方法是等效的
        $Model = M();
        // 计算总数
        $countstr = "SELECT count(*) as count FROM hisihi_school where school_schooltype_id = ".$typeid;
        $count = $Model->query($countstr);
        $total = $count[0]['count'];
        $totalpage = intval($total / $this->pagesize);//取整
        $page1 = $total % $this->pagesize;
        if($page1 > 0)
            $totalpage += 1;
        if($curpage > $totalpage)
            $curpage = $totalpage;
        if($curpage < 1)
            $curpage = 1;
        $limitindex = ($curpage-1)*$this->pagesize;

        $sqlstr = "SELECT * FROM hisihi_school where school_schooltype_id = ".$typeid." limit ".$limitindex.",".$this->pagesize;
        //进行原生的SQL查询
        $list = $Model->query($sqlstr);
        if($list){
            $resultlist = array();
            $resultlist["totalcount"] = $total;
            $resultlist["totalpage"] = $totalpage;
            $resultlist["curpage"] = $curpage;
            $resultlist["pagesize"] = $this->pagesize;
            $resultlist["data"] = $list;
            $this->apiSuccess("获取高校信息成功", null, $resultlist);
        }else{
            $this->apiError("未查询到高校信息");
        }
    }

    /**根据省id和类型id查询所有高校信息
     * @param int $provinceid   省id
     * @param int $typeid       类型id
     */
    public  function  schoolstrict($provinceid=1,$typeid = 1){
        //实例化空模型
        //或者使用M快捷方法是等效的
        $Model = M();
        // 计算总数
        $countstr = "SELECT count(*) as count FROM hisihi_school where school_pro_id = ".$provinceid." and school_schooltype_id = ".$typeid;
        $count = $Model->query($countstr);
        $total = $count[0]['count'];

        $sqlstr = "SELECT * FROM hisihi_school where school_pro_id = ".$provinceid." and school_schooltype_id = ".$typeid;
        //进行原生的SQL查询
        $list = $Model->query($sqlstr);
        if($list){
            $resultlist = array();
            $resultlist["totalcount"] = $total;
            $resultlist["data"] = $list;
            $this->apiSuccess("获取高校信息成功", null, $resultlist);
        }else{
            $this->apiError("未查询到高校信息");
        }
    }

    /**根据省id和类型id分页查询高校信息
     * @param int $provinceid   省id
     * @param int $typeid       类型id
     * @param int $curpage      当前页码
     */
    public  function  schoolstrictpaging($provinceid=1,$typeid = 1,$curpage=1){
        //实例化空模型
        //或者使用M快捷方法是等效的
        $Model = M();
        // 计算总数
        $countstr = "SELECT count(*) as count FROM hisihi_school where school_pro_id = ".$provinceid." and school_schooltype_id = ".$typeid;
        $count = $Model->query($countstr);
        $total = $count[0]['count'];
        $totalpage = intval($total / $this->pagesize);//取整
        $page1 = $total % $this->pagesize;
        if($page1 > 0)
            $totalpage += 1;
        if($curpage > $totalpage)
            $curpage = $totalpage;
        if($curpage < 1)
            $curpage = 1;
        $limitindex = ($curpage-1)*$this->pagesize;

        $sqlstr = "SELECT * FROM hisihi_school where school_pro_id = ".$provinceid." and school_schooltype_id = ".$typeid." limit ".$limitindex.",".$this->pagesize;
        //进行原生的SQL查询
        $list = $Model->query($sqlstr);
        if($list){
            $resultlist = array();
            $resultlist["totalcount"] = $total;
            $resultlist["totalpage"] = $totalpage;
            $resultlist["curpage"] = $curpage;
            $resultlist["pagesize"] = $this->pagesize;
            $resultlist["data"] = $list;
            $this->apiSuccess("获取高校信息成功", null, $resultlist);
        }else{
            $this->apiError("未查询到高校信息");
        }
    }

}