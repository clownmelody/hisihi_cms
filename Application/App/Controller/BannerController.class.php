<?php
namespace App\Controller;
use Common\Controller\BaseController;
use log\ApiInfoLog;
/**
 * Created by PhpStorm.
 * User: yangchujie
 * Date: 16/8/27
 * Time: 10:01
 */

class BannerController extends BaseController
{
    private $log;
    public function __construct(){
        parent::__construct();
        $this->log = new ApiInfoLog('BannerController');
    }

    /**
     * 获取3.0.1首页banner
     */
    public function index() {
        switch ($this->_method){
            case 'get':
                $list = D('App/Banner','Service')->getIndexBanner();
                $extra['data'] = $list;
                $extra['totalCount'] = count($list);
                $this->apiSuccess('获取app首页banner成功', null, $extra);
                break;
            default:
                $this->response_json('not support this method!');
                break;
        }
    }

}