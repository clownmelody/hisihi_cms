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
        $this->mail->Host = 'smtp.qq.com';
        $this->mail->SMTPAuth = true;
        $this->mail->Username = '1424627720@qq.com';
        $this->mail->Password = '18507554340';
        $this->mail->SMTPSecure = 'ssl';
        $this->mail->Port = 465;
        $this->mail->setFrom('1424627720@qq.com', '杨楚杰');
    }

    function sendMail($email=null, $path=null){
        if(!$path || !$email){
            return false;
        }
        $this->mail->addAddress($email);
        $this->mail->addAttachment($path, "简历.pdf");
        $this->mail->isHTML(true);

        $this->mail->Subject = '邮件系统测试';
        $this->mail->Body    = 'This is the HTML message body <b>in bold!</b>';
        $this->mail->AltBody = "为了查看该邮件，请切换到支持 HTML 的邮件客户端";

        if(!$this->mail->send()) {
            return false;
        } else {
            return true;
        }
    }

}