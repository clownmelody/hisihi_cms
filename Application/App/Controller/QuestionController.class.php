<?php
namespace App\Controller;
use Common\Controller\BaseController;

class QuestionController extends BaseController
{
    public function __construct(){
        parent::__construct();
    }

    public function _initialize(){
        C('SHOW_PAGE_TRACE', false);
    }

    public function getQuestions(){
        $model = M('Questions');
        $totalCount = $model->where('status=1')->count();
        $list = $model->field('title, content')->where('status=1')->select();
        $extra['totalCount'] = $totalCount;
        $extra['data'] = $list;
        $this->apiSuccess('获取常见问题列表成功', null, $extra);
    }

}