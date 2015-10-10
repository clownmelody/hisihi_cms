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
    function init(){
        $mail = new PHPMailer;
        $mail->CharSet ="UTF-8";
        $mail->isSMTP();                                      // Set mailer to use SMTP
        $mail->Host = 'smtp.qq.com';                        // Specify main and backup SMTP servers
        $mail->SMTPAuth = true;                               // Enable SMTP authentication
        $mail->Username = '1424627720@qq.com';                 // SMTP username
        $mail->Password = '18507554340';                       // SMTP password
        $mail->SMTPSecure = 'ssl';                            // Enable TLS encryption, `ssl` also accepted
        $mail->Port = 465;                                    // TCP port to connect to

        $mail->setFrom('1424627720@qq.com', '杨楚杰');
        $mail->addAddress('yangchujie1@163.com', 'walterYang');     // Add a recipient
        /*$mail->addAddress('ellen@example.com');               // Name is optional
        $mail->addReplyTo('info@example.com', 'Information');
        $mail->addCC('cc@example.com');
        $mail->addBCC('bcc@example.com');*/

        $mail->addAttachment('/Users/yangchujie/Downloads/积分规则.docx');     // Add attachments
        //$mail->addAttachment('http://pic21.nipic.com/20120522/9475712_132159134103_2.jpg', 'new.jpg');  // Optional name
        $mail->isHTML(true);                                  // Set email format to HTML

        $mail->Subject = '邮件系统测试....';
        $mail->Body    = 'This is the HTML message body <b>in bold!</b>';
        $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

        if(!$mail->send()) {
            echo 'Message could not be sent.';
            echo 'Mailer Error: ' . $mail->ErrorInfo;
        } else {
            echo 'Message has been sent';
        }
    }

}