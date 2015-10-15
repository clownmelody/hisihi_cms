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
        $this->pdf->SetDefaultMonospacedFont('courier');

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

        //设置字体
        $this->pdf->SetFont('stsongstdlight', '', 14);

        $this->pdf->AddPage();

        $userController = new UserController();
        $profile = $userController->getResumeProfile($uid);

        $nickname = $profile['info']['nickname'];
        $username = $profile['info']['username'];
        $avatar = $profile['info']['avatar256'];

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
        foreach ($experience_list as &$experience) {
            unset($experience['id']);
            unset($experience['uid']);
            unset($experience['status']);
        }

        $work_list = $profile['info']['works'];     //  用户作品

        $html = <<<EOF
        <style>
            h1 {
                color: navy;
                font-family: times;
                font-size: 24pt;
                text-decoration: underline;
            }
            p.first {
                color: #003300;
                font-family: helvetica;
                font-size: 12pt;
            }
            p.first span {
                color: #006600;
                font-style: italic;
            }
            p#second {
                color: rgb(00,63,127);
                font-family: times;
                font-size: 12pt;
                text-align: justify;
            }
            p#second > span {
                background-color: #FFFFAA;
            }
            table.first {
                color: #003300;
                font-family: helvetica;
                font-size: 8pt;
                border-left: 3px solid red;
                border-right: 3px solid #FF00FF;
                border-top: 3px solid green;
                border-bottom: 3px solid blue;
                background-color: #ccffcc;
            }
            td {
                border: 2px solid blue;
                background-color: #ffffee;
            }
            td.second {
                border: 2px dashed green;
            }
            div.test {
                color: #CC0000;
                background-color: #FFFF66;
                font-family: helvetica;
                font-size: 10pt;
                border-style: solid solid solid solid;
                border-width: 2px 2px 2px 2px;
                border-color: green #FF00FF blue red;
                text-align: center;
            }
            .lowercase {
                text-transform: lowercase;
            }
            .uppercase {
                text-transform: uppercase;
            }
            .capitalize {
                text-transform: capitalize;
            }
        </style>

        <h3 class="title">个人简历板式测试</h3>

        <p class="first">Example of paragraph with class selector. <span>Lorem ipsum dolor sit amet, consectetur adipiscing elit. In sed imperdiet lectus. Phasellus quis velit velit, non condimentum quam. Sed neque urna, ultrices ac volutpat vel, laoreet vitae augue. Sed vel velit erat. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Cras eget velit nulla, eu sagittis elit. Nunc ac arcu est, in lobortis tellus. Praesent condimentum rhoncus sodales. In hac habitasse platea dictumst. Proin porta eros pharetra enim tincidunt dignissim nec vel dolor. Cras sapien elit, ornare ac dignissim eu, ultricies ac eros. Maecenas augue magna, ultrices a congue in, mollis eu nulla. Nunc venenatis massa at est eleifend faucibus. Vivamus sed risus lectus, nec interdum nunc.</span></p>

        <p id="second">Example of paragraph with ID selector. <span>Fusce et felis vitae diam lobortis sollicitudin. Aenean tincidunt accumsan nisi, id vehicula quam laoreet elementum. Phasellus egestas interdum erat, et viverra ipsum ultricies ac. Praesent sagittis augue at augue volutpat eleifend. Cras nec orci neque. Mauris bibendum posuere blandit. Donec feugiat mollis dui sit amet pellentesque. Sed a enim justo. Donec tincidunt, nisl eget elementum aliquam, odio ipsum ultrices quam, eu porttitor ligula urna at lorem. Donec varius, eros et convallis laoreet, ligula tellus consequat felis, ut ornare metus tellus sodales velit. Duis sed diam ante. Ut rutrum malesuada massa, vitae consectetur ipsum rhoncus sed. Suspendisse potenti. Pellentesque a congue massa.</span></p>

        <div class="test">example of DIV with border and fill.
        <br />Lorem ipsum dolor sit amet, consectetur adipiscing elit.
        <br /><span class="lowercase">text-transform <b>LOWERCASE</b> Lorem ipsum dolor sit amet, consectetur adipiscing elit.</span>
        <br /><span class="uppercase">text-transform <b>uppercase</b> Lorem ipsum dolor sit amet, consectetur adipiscing elit.</span>
        <br /><span class="capitalize">text-transform <b>cAPITALIZE</b> Lorem ipsum dolor sit amet, consectetur adipiscing elit.</span>
        </div>

        <br />
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