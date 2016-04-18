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