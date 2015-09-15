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
        //实例化空模型
        //或者使用M快捷方法是等效的
        $Model = M();
        //进行原生的SQL查询
        $list = $Model->query('SELECT * FROM hisihi_province');
        if($list){
            $resultlist = array();
            $resultlist["data"] = $list;
            $this->apiSuccess("获取省信息成功", null, $resultlist);
        }else{
            $this->apiError("未查询到省信息");
        }
    }

    /**根据省id查询所有高校信息
     * @param int $provinceid   省id
     */
    public  function  school($provinceid = 1){
        //实例化空模型
        //或者使用M快捷方法是等效的
        $Model = M();
        // 计算总数
        $countstr = "SELECT count(*) as count FROM hisihi_school where school_pro_id = ".$provinceid;
        $count = $Model->query($countstr);
        $total = $count[0]['count'];

        $sqlstr = "SELECT * FROM hisihi_school where school_pro_id = ".$provinceid;
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

    /**根据省id分页查询高校信息
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

    /**分页查询所有高校信息
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