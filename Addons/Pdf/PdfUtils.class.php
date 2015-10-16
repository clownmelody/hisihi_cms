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

	function init(){
		$this->pdf->SetCreator('嘿设汇');
		$this->pdf->SetAuthor('hisihi');
		$this->pdf->SetTitle('个人简历');
		$this->pdf->SetSubject('子标题');
		$this->pdf->SetKeywords('简历, 嘿设汇, PDF,');

		$this->pdf->setPrintHeader(false);
		$this->pdf->setPrintFooter(false);

		// 设置默认等宽字体
		$this->pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
        $this->pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $this->pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $this->pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
        $this->pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
        $this->pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

		//设置字体
		$this->pdf->SetFont('stsongstdlight', '', '');

		$this->pdf->AddPage();

		$this->pdf->setCellPaddings(1, 1, 1, 1);

		$this->pdf->setCellMargins(1, 1, 1, 1);

		$this->pdf->SetFillColor(255, 255, 127);

		$txt = 'Lorem ipsum dolor sit amet, consectetur adipisicing eli';

		$this->pdf->MultiCell(55, 5, '[LEFT] '.$txt, 1, 'L', 1, 0, '', '', true);
		$this->pdf->MultiCell(55, 5, '[RIGHT] '.$txt, 1, 'R', 0, 1, '', '', true);
		$this->pdf->MultiCell(55, 5, '[CENTER] '.$txt, 1, 'C', 0, 0, '', '', true);
		$this->pdf->MultiCell(55, 5, '[JUSTIFY] '.$txt."\n", 1, 'J', 1, 2, '' ,'', true);
		$this->pdf->MultiCell(55, 5, '[DEFAULT] '.$txt, 1, '', 0, 1, '', '', true);

		$time = time();
		$path = '/tmp/'.$time.'.pdf';
		//输出PDF
		$this->pdf->Output($path, 'I');
	}

    function _winit($uid=0){
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
        /*$this->pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
        $this->pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $this->pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $this->pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
        $this->pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
        $this->pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);*/

        //设置字体
        $this->pdf->SetFont('stsongstdlight', '', '');

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

        $education_list = '<p class="txt"><span>'.$collage.'</span><span>   '.$major.'</span></p>';

        $experience_list = $profile['info']['experience'];  //  工作经历
        $experience_list_ele = null;
        foreach ($experience_list as &$experience) {
            $start_time = date('Y-m-d', $experience['start_time']);
            $end_time = date('Y-m-d', $experience['end_time']);
            $time = $start_time.'--'.$end_time;
            $str = '<p class="txt word"><span>'.$time.'  </span><span>'.$experience["company_name"].'   '.$experience["department"].'</span></p><p class="txt">'.$experience['job_content'].'</p>';
            $experience_list_ele = $experience_list_ele . $str;
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
<body style="width: 960px;margin: 0 auto;background: #fff;">

	<div class="main" style="margin: 45px 71px 90px;">

		<div class="box user" style="padding: 40px 39px;border-bottom: 1px solid #D8D9DB;position: relative;">
			<p class="title" style="font-size: 24px;font-weight: bold;margin-bottom: 36px;color: #666;">设计师</p>
			<img src="http://pic.yupoo.com/hiskyido/EZpoKkNf/N0bu3.jpg" alt="" class="user-img" style="width: 150px;height: 150px;position: absolute;top: 0;right: 20px;">
			<p class="txt" style="font-size: 12px;color: #999;">
				<span style="margin-right: 24px;line-height: 25px;">男</span>
				<span style="margin-right: 24px;line-height: 25px;">25岁</span>
				<span style="margin-right: 24px;line-height: 25px;">UI设计师</span>
			</p>
			<p class="txt" style="font-size: 12px;color: #999;">
				<span style="margin-right: 24px;line-height: 25px;">12345678901</span>
				<span style="margin-right: 24px;line-height: 25px;">123456789@qq.com</span>
			</p>
		</div>
		<!-- /.box -->

		<div class="box" style="padding: 40px 39px;border-bottom: 1px solid #D8D9DB;">
			<p class="title" style="font-size: 14px;color: #666;margin-bottom: 24px;font-weight: bold;">教育经历</p>
			<p class="txt" style="font-size: 12px;color: #999;">
				<span style="margin-right: 24px;line-height: 25px;">2012.05——至今</span>
				<span style="margin-right: 24px;line-height: 25px;">华中农业大学</span>
				<span style="margin-right: 24px;line-height: 25px;">UI设计专业</span>
			</p>
		</div>
		<!-- /.box -->

		<div class="box" style="padding: 40px 39px;border-bottom: 1px solid #D8D9DB;">
			<p class="title" style="font-size: 14px;color: #666;margin-bottom: 24px;font-weight: bold;">自我评价</p>
			<p class="txt icon"  style="font-size: 12px;color: #999;margin-bottom: 15px;">
				<span style="margin-right: 24px;background: #e5e5e5;border-radius: 50px;padding: 3px 8px;color: #666;line-height: 40px;">独当一面</span>
				<span style="margin-right: 24px;background: #e5e5e5;border-radius: 50px;padding: 3px 8px;color: #666;line-height: 40px;">完美主义</span>
				<span style="margin-right: 24px;background: #e5e5e5;border-radius: 50px;padding: 3px 8px;color: #666;line-height: 40px;">极客精神</span>
				<span style="margin-right: 24px;background: #e5e5e5;border-radius: 50px;padding: 3px 8px;color: #666;line-height: 40px;">思维慎密</span>
			</p>
			<p class="txt"  style="font-size: 12px;color: #999;">我有良好的美术基础受过系统的计算机艺术设计专业知识训练并仍在设计方面不断的学习中，计算机艺术设计专业知识训练仍在学习中</p>
		</div>
		<!-- /.box -->

		<div class="box" style="padding: 40px 39px;border-bottom: 1px solid #D8D9DB;">
			<p class="title" style="font-size: 14px;color: #666;margin-bottom: 24px;font-weight: bold;">工作经验</p>
			<p class="txt word"  style="font-size: 12px;margin-bottom: 10px;font-weight: bold;color: #666;">
				<span style="margin-right: 24px;line-height: 25px;">2012.05——至今</span>
				<span style="margin-right: 24px;line-height: 25px;">腾讯新闻事业部</span>
			</p>
			<p class="txt"  style="font-size: 12px;color: #999;">我有良好的美术基础受过系统的计算机艺术设计专业知识训练并仍在设计方面不断的学习中，计算机艺术设计专业知识训练仍在学习中</p>
		</div>
		<!-- /.box -->

		<div class="box img" style="padding: 40px 39px;border-bottom: 1px solid #D8D9DB;padding-bottom: 50px;position: relative;">
			<p class="title" style="font-size: 14px;color: #666;margin-bottom: 24px;font-weight: bold;">作品展示</p>
			<img src="http://pic.yupoo.com/hiskyido/EZpoJdCA/medish.jpg" alt="" style="max-width: 626px;margin-bottom: 20px;">
			<img src="http://pic.yupoo.com/hiskyido/EZpoKkNf/N0bu3.jpg" alt="" style="max-width: 626px;margin-bottom: 20px;">
			<div class="foot" style="position: absolute; bottom: -60px;left: 0;right: 0;text-align: center;">
				<div class="foot-img" style="background: #fff;padding: 20px;margin: 0 auto; width: 140px;">
					<img src="http://hisihi-other.oss-cn-qingdao.aliyuncs.com/2015-10-15/icon_pdf_logo.png" alt="">
				</div>
			</div>
		</div>
		<!-- /.box -->
	</div>
</body>
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