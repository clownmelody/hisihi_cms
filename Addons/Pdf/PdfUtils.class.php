<?php
/**
 * Created by PhpStorm.
 * User: yangchujie
 * Date: 15/10/10
 * Time: 15:25
 */

namespace Addons\Pdf;
use App\Controller\UserController;
use TCPDF;
require_once './Addons/Pdf/tcpdf/tcpdf.php';

class PdfUtils
{
    private $pdf;

    public function __construct() {
        $this->pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
    }

    function init($uid=0){
        if(!$uid){
            return false;
        }
        // 设置文档信息
        $this->pdf->SetCreator('嘿设汇');
        $this->pdf->SetAuthor('hisihi');
        $this->pdf->SetTitle('个人简历');
        $this->pdf->SetSubject('子标题');
        $this->pdf->SetKeywords('简历, 嘿设汇, PDF,');

        $this->pdf->setPrintHeader(false);
        $this->pdf->setPrintFooter(false);

        // 设置默认等宽字体
        /*$this->pdf->SetDefaultMonospacedFont('courier');

        // 设置间距
        $this->pdf->SetMargins(15, 20, 15);
        $this->pdf->SetHeaderMargin(5);
        $this->pdf->SetFooterMargin(10);

        // 设置分页
        $this->pdf->SetAutoPageBreak(TRUE, 10);

        // set image scale factor
        $this->pdf->setImageScale(1.25);

        // set default font subsetting mode
        $this->pdf->setFontSubsetting(true);
        */
        //设置字体
        $this->pdf->SetFont('stsongstdlight', '', 14);

        $this->pdf->AddPage();

        $userController = new UserController();
        $profile = $userController->getResumeProfile($uid);

        $nickname = $profile['info']['nickname'];
        $username = $profile['info']['username'];
        $avatar = $profile['info']['avatar256'];
        $mobile = $profile['info']['mobile'];
        $email = $profile['info']['email'];
        $sex = $profile['info']['sex'];
        if($sex==0){
            $sex = "未填写";
        } else if($sex==1){
            $sex = "男";
        } else {
            $sex = "女";
        }
        $birthday = $profile['info']['birthday'];

        $extinfo_list = $profile['info']['extinfo'];
        foreach($extinfo_list as $extinfo){
            switch($extinfo['field_name']){
                case 'college':  // 大学
                    $collage = $extinfo['field_content'];
                    break;
                case 'major':    // 专业
                    $major = $extinfo['field_content'];
                    break;
                case 'grade':    // 年级
                    $grade = $extinfo['field_content'];
                    break;
                case 'study_institution':    // 学习机构
                    $study_institution = $extinfo['field_content'];
                    break;
                case 'skills':    // 软件技能
                    $skills = $extinfo['field_content'];
                    break;
                case 'expected_position':    // 期望职位
                    $expected_position = $extinfo['field_content'];
                    break;
                case 'my_highlights':    // 我的亮点
                    $my_highlights = $extinfo['field_content'];
                    break;
                case 'my_strengths':    // 我的优势
                    $my_strengths = $extinfo['field_content'];
                    break;
            }
        }

        $experience_list = $profile['info']['experience'];  //  工作经历
        $experience_list_ele = null;
        foreach ($experience_list as &$experience) {
            $start_time = date('Y-m-d', $experience['start_time']);
            $end_time = date('Y-m-d', $experience['end_time']);
            $time = $start_time.'--'.$end_time;
            $str = '<p class="txt"><span>'.$time.'</span><span>'.$experience["company_name"].'</span><span>'.$experience["department"].'</span></p>';
            $experience_list_ele = $experience_list_ele + $str;
            unset($experience['id']);
            unset($experience['uid']);
            unset($experience['status']);
        }

        $work_list = $profile['info']['works'];     //  用户作品
        $work_list_ele = null;
        foreach($work_list as $work){
            $str =  '<img src="'. $work['src'] .'">';
            $work_list_ele = $work_list_ele . $str;
        }

        $html = <<<EOF
        <!DOCTYPE html>
<html lang="zh-CN">

<head>
	<title>嘿设汇</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="Resource-type" content="Document" />
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" />
	<meta http-equiv="X-UA-Compatible" content="IE=Edge">
	<meta http-equiv="Expires" content="0">
	<meta http-equiv="Pragma" content="no-cache">
	<meta http-equiv="Cache-control" content="no-cache">
	<meta http-equiv="Cache" content="no-cache">
	<style>
	*::after, *::before {
	    box-sizing: border-box;
	}
	ul{
		 list-style-type: none;
	}
	*{
		margin: 0;
		padding: 0;
	}
	body{
		background: #fff;
	}
	.main{
		margin: 45px 45px 90px;
	}
	@media (min-width: 240px) and (max-width: 640px) {
	  .main{
	  		margin: 25px 10px 60px;
	  	}
	}

	.box{
		padding: 30px 25px;
		border-bottom: 1px solid #D8D9DB;
	}
	.img{
		padding-bottom: 50px;
		position: relative;
	}
	.box >img{
		max-width: 100%;
		margin-bottom: 20px;
	}
	.title{
		font-size: 18px;
		color: #454546;
		margin-bottom: 20px;
		font-weight: bold;
	}
	.txt{
		font-size: 14px;
		color: #707072;
	}
	span{
		margin-right: 20px;
		line-height: 25px;
	}
	.icon{
		margin-bottom: 15px;
	}
	.icon span{
		background: #E6E7E9;
		border-radius: 50px;
		padding: 3px 8px;
		color: #5A5B5C;
		line-height: 35px;
	}
	.word{
		font-size: 16px;
		margin-bottom: 10px;
		font-weight: bold;
		color: #57585A;
	}
	.foot{
		position: absolute;
		bottom: -60px;
		left: 0;
		right: 0;
		text-align: center;
	}
	.foot-img{
		background: #fff;
		padding: 20px;
		margin: 0 auto;
		width: 140px;
	}
	.user{
		position: relative;
	}
	.user .title{
		font-size: 30px;
		font-weight: normal;
		margin-bottom: 15px;
	}
	.user-img{
		width: 125px;
		height: 125px;
		position: absolute;
		top: 0;
		right: 20px;
	}
	@media (min-width: 240px) and (max-width: 640px) {
	  	.user-img{
	  		width: 90px;
	  		height: 90px;
	  		right: 10px;
	  	}
	}
	</style>
</head>
<body>
	<div class="main">
		<div class="box user">
			<p class="title">$nickname</p>
			<img src="$avatar" alt="" class="user-img">
			<p class="txt">
				<span>$sex</span>
				<span>$birthday</span>
				<span>$expected_position</span>
			</p>
			<p class="txt">
				<span>$mobile</span>
				<span>$email</span>
			</p>
		</div>
		<!-- /.box -->

		<div class="box">
			<p class="title">教育经历</p>
			<p class="txt">
				<span>2012.05——至今</span>
				<span>华中农业大学</span>
				<span>UI设计专业</span>
			</p>
		</div>
		<!-- /.box -->

		<div class="box">
			<p class="title">自我评价</p>
			<p class="txt icon">
				<span>独当一面</span>
				<span>完美主义</span>
				<span>极客精神</span>
				<span>思维慎密</span>
			</p>
			<p class="txt">我有良好的美术基础受过系统的计算机艺术设计专业知识训练并仍在设计方面不断的学习中，计算机艺术设计专业知识训练仍在学习中</p>
		</div>
		<!-- /.box -->

		<div class="box">
			<p class="title">工作经验</p>
			<p class="txt word">
				<span>2012.05——至今</span>
				<span>腾讯新闻事业部</span>
			</p>
			<p class="txt">我有良好的美术基础受过系统的计算机艺术设计专业知识训练并仍在设计方面不断的学习中，计算机艺术设计专业知识训练仍在学习中</p>
		</div>
		<!-- /.box -->

		<div class="box img">
			<p class="title">作品展示</p>
			<img src="http://pic.yupoo.com/hiskyido/EZpoJdCA/medish.jpg" alt="">
			<img src="http://pic.yupoo.com/hiskyido/EZpoKkNf/N0bu3.jpg" alt="">
			<div class="foot">
				<div class="foot-img">
					<img src="http://hisihi-other.oss-cn-qingdao.aliyuncs.com/2015-10-15/icon_pdf_logo.jpg" alt="">
				</div>
			</div>
		</div>
		<!-- /.box -->
	</div>
</body>
</html>
EOF;

        $this->pdf->writeHTML($html, true, false, true, false, '');

        $time = time();
        $path = '/tmp/'.$time.'.pdf';
        //输出PDF
        $this->pdf->Output($path, 'I');
        //$this->pdf->Output($path, 'F');
        return $path;
    }

}