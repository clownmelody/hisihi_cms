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
		$this->pdf->SetFont('stsongstdlight', '', 13);

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
		list($by,$bm,$bd)=explode('-',$birthday);
		$cm=date('n');
		$cd=date('j');
		$age=date('Y')-$by-1;
		if ($cm>$bm || $cm==$bm && $cd>$bd) $age++;

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

		$my_highlights = json_decode($my_highlights, true);
		$highlights = null;
		foreach($my_highlights as $light) {
			$str = '<span style="margin-right: 24px;background: #e5e5e5;border-radius: 50px;padding: 3px 8px;color: #666;">' . $light["value"] . '</span>';
			$highlights = $highlights . $str . "&nbsp;&nbsp;";
		}

		$experience_list = $profile['info']['experience'];  //  工作经历
		$experience_list_ele = null;
		foreach ($experience_list as &$experience) {
			$start_time = date('Y-m-d', $experience['start_time']);
			$end_time = date('Y-m-d', $experience['end_time']);
			$time = $start_time.'--'.$end_time;
			$str = '<p class="txt word" style="font-size: 12px;margin-bottom: 10px;font-weight: bold;color: #666;"><span>'.$time.'  </span><span>'.$experience["company_name"].'   '.$experience["department"].'</span></p><p class="txt" style="font-size: 12px;color: #999;">'.$experience['job_content'].'</p>';
			$experience_list_ele = $experience_list_ele . $str;
			unset($experience['id']);
			unset($experience['uid']);
			unset($experience['status']);
		}

		$work_list = $profile['info']['works'];     //  用户作品
		$work_list_ele = null;
		foreach($work_list as $work){
			$str =  '<img src="'. $work['src'] .'" style="max-width: 626px;margin-bottom: 20px;">';
			$work_list_ele = $work_list_ele . $str;
		}

		$html = <<<EOF
<table cellspacing="0" cellpadding="1" border="0">
    <tr style="line-height: 25px;">
        <td style="width: 27%;font-size: 14px;color: #666;margin-bottom: 24px;font-weight: bold;">$nickname</td>
        <td style="width: 27%;"></td>
        <td style="width: 28%;"></td>
        <td style="width: 18%;" rowspan="3"><img src="$avatar" alt="" class="user-img" style="width: 80px;height: 100px;"></td>
    </tr>
    <tr style="line-height: 25px;">
        <td colspan="2" style="font-size: 14px;color: #666;margin-bottom: 24px;font-weight: bold;">$sex&nbsp;&nbsp;&nbsp;$age&nbsp;&nbsp;&nbsp;$expected_position</td>
    </tr>
    <tr style="line-height: 30px;">
       <td colspan="3" style="font-size: 14px;color: #666;margin-bottom: 24px;font-weight: bold;">$mobile&nbsp;&nbsp;&nbsp;$email</td>
    </tr>

</table>
EOF;
		$this->pdf->writeHTML($html, true, false, false, 0);

		$html = <<<part1
<div class="box" style="border-bottom: 1px solid #D8D9DB;">
		</div>
part1;
		$this->pdf->writeHTML($html, true, false, false, 0);

		$html = <<<part1
<div class="box" style="padding: 40px 39px;border-bottom: 1px solid #D8D9DB;">
			<p class="title" style="font-size: 14px;color: #666;margin-bottom: 24px;font-weight: bold;">教育经历</p>
			<p class="txt" style="font-size: 12px;color: #999;">
				<span style="margin-right: 24px;line-height: 25px;">$collage</span>
				<span style="margin-right: 24px;line-height: 25px;">$major</span>
			</p>
		</div>
part1;
		$this->pdf->writeHTML($html, true, false, false, 0);

		$html = <<<part1
<div class="box" style="padding: 40px 39px;border-bottom: 1px solid #D8D9DB;">
			<p class="title" style="font-size: 14px;color: #666;margin-bottom: 24px;font-weight: bold;">自我评价</p>
			<p class="txt icon"  style="font-size: 12px;color: #999;margin-bottom: 15px;">
part1;
		$html = $html . $highlights . '</p><p class="txt" style="font-size: 12px;color: #999;">'.$my_strengths.'</p></div>';
		$this->pdf->writeHTML($html, true, false, false, 0);

		$html = <<<part1
<div class="box" style="padding: 40px 39px;border-bottom: 1px solid #D8D9DB;">
			<p class="title" style="font-size: 14px;color: #666;margin-bottom: 24px;font-weight: bold;">工作经验</p>
part1;
		$html = $html . $experience_list_ele . "</div>";
		$this->pdf->writeHTML($html, true, false, false, 0);

		$html = <<<part1
<div class="box img" style="padding: 40px 39px;border-bottom: 1px solid #D8D9DB;padding-bottom: 50px;position: relative;">
			<p class="title" style="font-size: 14px;color: #666;margin-bottom: 24px;font-weight: bold;">作品展示</p>
part1;
		$this->pdf->writeHTML($html, true, false, false, 0);

		$this->pdf->writeHTML($work_list_ele, true, false, false, 0);

		$html = <<<part1
<div class="foot" style="position: absolute; bottom: -60px;left: 0;right: 0;text-align: center;">
				<div class="foot-img" style="background: #fff;padding: 20px;margin: 0 auto; width: 140px;">
					<img src="http://hisihi-other.oss-cn-qingdao.aliyuncs.com/2015-10-15/icon_pdf_logo.png" alt="">
				</div>
			</div>
		</div>
part1;
		$this->pdf->writeHTML($html, true, false, false, 0);

		$time = time();
		$path = '/tmp/'.$time.'.pdf';
		$this->pdf->Output($path, 'F');
		return $path;
	}

}