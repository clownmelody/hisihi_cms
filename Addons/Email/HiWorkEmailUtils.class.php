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

class HiWorkEmailUtils
{
    private $mail;

    public function __construct() {
        $this->mail = new PHPMailer;
        $this->mail->CharSet ="UTF-8";
        $this->mail->isSMTP();
        $this->mail->Host = 'smtp.exmail.qq.com';
        $this->mail->SMTPAuth = true;
        $this->mail->Username = 'hisihi_busywork@hisihi.com';
        $this->mail->Password = 'Heishehui520';
        $this->mail->SMTPSecure = 'ssl';
        $this->mail->Port = 465;
        $this->mail->setFrom('hisihi_busywork@hisihi.com', '嘿设汇');
    }

    function sendMail($email=null, $value=null){
		if(!$email){
			$email = "hisihi_busywork@hisihi.com";
		}
        if(!$value){
            return false;
        }
        $this->mail->addAddress($email);
        //$this->mail->addAttachment($value['path'], "简历.pdf");
        $this->mail->isHTML(true);

        $this->mail->Subject = '来自嘿设汇的云作业';
        $email_content = <<<EOF
        这是一个寒风凛冽的季节，程序猿 Peter 像一只被打败的西伯利亚流浪犬，心里白茫茫一片，像被冰雪笼罩着的阿拉斯加。
        今天他向产品要邮件模板，但是失败了。无奈之中，他只好.................... 你懂得，反正云作业下载链接给你了，
        就在下面: <br/>
$value
EOF;

        $this->mail->Body = $email_content;
        $this->mail->AltBody = "为了查看该邮件，请切换到支持 HTML 的邮件客户端";

        if(!$this->mail->send()) {
            return false;
        } else {
            return true;
        }
    }

}