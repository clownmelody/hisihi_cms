<?php
/**
 * Created by PhpStorm.
 * User: yangchujie
 * Date: 15/10/10
 * Time: 15:25
 */

namespace Addons\Email;
use PHPMailer;

require_once './Addons/Email/PHPMailer/PHPMailerAutoload.php';

class EmailUtils
{
    private $mail;

    public function __construct() {
        $this->mail = new PHPMailer;
        $this->mail->CharSet ="UTF-8";
        $this->mail->isSMTP();
        $this->mail->Host = 'smtp.exmail.qq.com';
        $this->mail->SMTPAuth = true;
        $this->mail->Username = 'resume@hisihi.com';
        $this->mail->Password = '027Hisihi';
        $this->mail->SMTPSecure = 'ssl';
        $this->mail->Port = 465;
        $this->mail->setFrom('resume@hisihi.com', '嘿设汇');
    }

    function sendMail($email=null, $value=null){
		if(!$email){//简历统一发送到嘿设汇指定邮箱，再由人工手动发送到各公司
			$email = "receive_resume@hisihi.com";
		}
        if(!$value){
            return false;
        }
        $this->mail->addAddress($email);
        $this->mail->addAttachment($value['path'], "简历.pdf");
        $this->mail->isHTML(true);

        $this->mail->Subject = '来自嘿设汇的简历投递';
        $email_content = <<<EOF
<!DOCTYPE html>
<html lang="zh-CN">

<head>
	<title>嘿设汇个人简历</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="Resource-type" content="Document" />
	<!-- <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" /> -->
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
		background: #fafafa;
	}
	.top{
		background-color: #212121;
		width: 100%;
		height: 88px;
		position: relative;
		margin-bottom: 40px;
	}

	.top >.top-img{
		background: url("http://hisihi-other.oss-cn-qingdao.aliyuncs.com/hotkeys/bg_email_title.png");
		background-repeat: no-repeat;
		width: 100%;
		height: 88px;
	}
	.top >.top-box{
		position: absolute;
		top: 0;
		left: 0;
		right: 0;
		padding-top: 20px;
		height: 68px;
	}
	.top-box >.top-txt{
		line-height: 25px;
		margin: 0 40px 0 0;
		padding: 0;
		text-align: right;
		font-size: 14px;
		color: #fff;
	}
	.main{
		padding: 0 30px;
		border-bottom: 1px solid #eee;
		font-size: 16px;
		color: #616161;
	}
	.main-title{
		margin-bottom: 40px;
	}
	.main-txt{
		margin-bottom: 24px;
	}
	.user-resume{
		padding: 24px;
		margin-bottom: 24px;

	}
	.user-resume >p{
		font-size: 18px;
		color: #616161;
		line-height: 35px;
	}
	.user-resume >.resume-title{
		font-size: 20px;
		font-weight: bold;
	}
	.two-code{
		margin-top: 32px;
		margin-bottom: 48px;
	}
	.two-code >img{
		float: left;
		width: 128px;
		height: 128px;
		margin-right: 24px;
	}
	.two-code >.code-box{
		padding-top: 50px;
	}
	.code-box >p{
		line-height: 25px;
		font-size: 14px;
		color: #61616;
	}
	.foot{
		margin: 16px 30px 24px;
	}
	.foot >p{
		font-size: 12px;
		color: #616161;
		line-height: 25px;
	}
	.bold{
		font-weight: bold;
	}
</style>

</head>
<body>
	<div class="top">
		<div class="top-img"></div>
		<div class="top-box">
			<p class="top-txt">
				嘿设汇已帮助325家企业
			</p>
			<p class="top-txt">
				找到1253个优秀设计师
			</p>
		</div>
	</div>

	<div class="main">
EOF;

     $data_content = '<p class="main-title">
			<span class="bold">'.$value['company_name'].'</span> 的 <span class="bold">HR</span>，您好!
		</p>
		<p class="main-txt">来自嘿设汇平台的设计师 <span class="blod">'.$value['nickname'].'</span> 向贵公司投递了一封 <span class="blod">'.$value['expected_position'].'</span> 职位的求职简历，相关简历详情请参考附件。</p>
		<div class="user-resume">
			<p class="resume-title">'.$value['nickname'].'的简历</p>
			<p>'.$value['nickname'].' | '.$value['sex'].' | '.$value['age'].'岁 | '.$value['expected_position'].'</p>
			<p>教育经历: '.$value['college'].'</p>
			<p>联系方式: '.$value['mobile'].' | '.$value['email'].'</p>
		</div>
		<p class="main-txt">若您对该简历感兴趣，请与'.$value['nickname'].'进行联系，或直接回复邮件，告知相关面试信息。</p>';

    $end_content = <<<ENT
		<div class="two-code">
            <img src="http://hisihi-other.oss-cn-qingdao.aliyuncs.com/hotkeys/hisihi_email_logo.png" alt="嘿设汇二维码">
			<div class="code-box">
				<p>找设计师就上嘿设汇！</p>
				<p>扫描左侧二维码，</p>
				<p>即可下载嘿设汇，找最好的设计师。</p>
			</div>
		</div>

	</div>

	<div class="foot">
		<p>如有任何问题，可以与我们联系。我们将尽快为您解答</p>
		<p>Email：support@hisihi.com，电话：40003-40033，微信：hisihi-com</p>
	</div>

</body>
</html>
ENT;
        $this->mail->Body = $email_content.$data_content.$end_content;
        $this->mail->AltBody = "为了查看该邮件，请切换到支持 HTML 的邮件客户端";

        if(!$this->mail->send()) {
            return false;
        } else {
            return true;
        }
    }

}