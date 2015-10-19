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
        if(!$value || !$email){
            return false;
        }
        $this->mail->addAddress($email);
        $this->mail->addAttachment($value['path'], "简历.pdf");
        $this->mail->isHTML(true);

        $this->mail->Subject = '来自嘿设汇的简历投递';
        $this->mail->Body    = '数据测试--公司名称:'.$value['company_name'].', data:'.json_encode($value);
        $this->mail->AltBody = "为了查看该邮件，请切换到支持 HTML 的邮件客户端";

        if(!$this->mail->send()) {
            echo "Mailer Error: " . $this->mail->ErrorInfo;
            return false;
        } else {
            return true;
        }
    }

}