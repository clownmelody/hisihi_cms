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

        // 设置页眉和页脚信息
        $this->pdf->SetHeaderData('logo_example.png', 10, 'hisihi.com', '通过嘿设汇投递的简历',
            array(0,64,255), array(0,64,128));
        $this->pdf->setFooterData(array(0,64,0), array(0,64,128));

        // 设置页眉和页脚字体
        $this->pdf->setHeaderFont(Array('stsongstdlight', '', '10'));
        $this->pdf->setFooterFont(Array('helvetica', '', '8'));

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

        $str1 = '姓名: ' . $profile['info']['nickname'];

        //$this->pdf->Write(0, $str1,'', 0, 'C', true, 0, false, false, 0);
        /*$this->pdf->Image($profile['info']['avatar256'], 25, 23, 20, 25);

        $this->pdf->setCellPaddings(1, 1, 1, 1);

        $this->pdf->setCellMargins(1, 1, 1, 1);

        $this->pdf->MultiCell(20, 25, '', 1, 'L', 0, 0, 25, 23, true);
        $this->pdf->MultiCell(55, 5, $str1, 1, 'R', 0, 1, '', '', true);*/
        $html = <<<EOF
        <!-- EXAMPLE OF CSS STYLE -->
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

        <table class="first" cellpadding="4" cellspacing="6">
         <tr>
          <td width="30" align="center"><b>No.</b></td>
          <td width="140" align="center" bgcolor="#FFFF00"><b>XXXX</b></td>
          <td width="140" align="center"><b>XXXX</b></td>
          <td width="80" align="center"> <b>XXXX</b></td>
          <td width="80" align="center"><b>XXXX</b></td>
          <td width="45" align="center"><b>XXXX</b></td>
         </tr>
         <tr>
          <td width="30" align="center">1.</td>
          <td width="140" rowspan="6" class="second">XXXX<br />XXXX<br />XXXX<br />XXXX<br />XXXX<br />XXXX<br />XXXX<br />XXXX</td>
          <td width="140">XXXX<br />XXXX</td>
          <td width="80">XXXX<br />XXXX</td>
          <td width="80">XXXX</td>
          <td align="center" width="45">XXXX<br />XXXX</td>
         </tr>
         <tr>
          <td width="30" align="center" rowspan="3">2.</td>
          <td width="140" rowspan="3">XXXX<br />XXXX</td>
          <td width="80">XXXX<br />XXXX</td>
          <td width="80">XXXX<br />XXXX</td>
          <td align="center" width="45">XXXX<br />XXXX</td>
         </tr>
         <tr>
          <td width="80">XXXX<br />XXXX<br />XXXX<br />XXXX</td>
          <td width="80">XXXX<br />XXXX</td>
          <td align="center" width="45">XXXX<br />XXXX</td>
         </tr>
         <tr>
          <td width="80" rowspan="2" >XXXX<br />XXXX<br />XXXX<br />XXXX<br />XXXX<br />XXXX<br />XXXX<br />XXXX</td>
          <td width="80">XXXX<br />XXXX</td>
          <td align="center" width="45">XXXX<br />XXXX</td>
         </tr>
         <tr>
          <td width="30" align="center">3.</td>
          <td width="140">XXXX<br />XXXX</td>
          <td width="80">XXXX<br />XXXX</td>
          <td align="center" width="45">XXXX<br />XXXX</td>
         </tr>
         <tr bgcolor="#FFFF80">
          <td width="30" align="center">4.</td>
          <td width="140" bgcolor="#00CC00" color="#FFFF00">XXXX<br />XXXX</td>
          <td width="80">XXXX<br />XXXX</td>
          <td width="80">XXXX<br />XXXX</td>
          <td align="center" width="45">XXXX<br />XXXX</td>
         </tr>
        </table>
EOF;

        $this->pdf->writeHTML($html, true, false, true, false, '');

        $time = time();
        $path = '/tmp/'.$time.'.pdf';
        //输出PDF
        $this->pdf->Output($path, 'I');
        return $path;
    }

}