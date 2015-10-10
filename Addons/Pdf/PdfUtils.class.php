<?php
/**
 * Created by PhpStorm.
 * User: yangchujie
 * Date: 15/10/10
 * Time: 15:25
 */

namespace Addons\Pdf;
use TCPDF;
require_once './Addons/Pdf/tcpdf/tcpdf.php';

class PdfUtils
{
    private $pdf;

    public function __construct() {
        $this->pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
    }

    function init(){
        // 设置文档信息
        $this->pdf->SetCreator('嘿设汇');
        $this->pdf->SetAuthor('hisihi');
        $this->pdf->SetTitle('标题');
        $this->pdf->SetSubject('子标题');
        $this->pdf->SetKeywords('关键字, PDF, PHP');

        // 设置页眉和页脚信息
        $this->pdf->SetHeaderData('logo_example.png', 30, 'hisihi.com', '来自嘿设汇的简历',
            array(0,64,255), array(0,64,128));
        $this->pdf->setFooterData(array(0,64,0), array(0,64,128));

        // 设置页眉和页脚字体
        $this->pdf->setHeaderFont(Array('stsongstdlight', '', '10'));
        $this->pdf->setFooterFont(Array('helvetica', '', '8'));

        // 设置默认等宽字体
        $this->pdf->SetDefaultMonospacedFont('courier');

        // 设置间距
        $this->pdf->SetMargins(15, 27, 15);
        $this->pdf->SetHeaderMargin(5);
        $this->pdf->SetFooterMargin(10);

        // 设置分页
        $this->pdf->SetAutoPageBreak(TRUE, 25);

        // set image scale factor
        $this->pdf->setImageScale(1.25);

        // set default font subsetting mode
        $this->pdf->setFontSubsetting(true);

        //设置字体
        $this->pdf->SetFont('stsongstdlight', '', 14);

        $this->pdf->AddPage();

        $str1 = '嘿设汇简历生成测试';

        $this->pdf->Write(0, $str1,'', 0, 'L', true, 0, false, false, 0);

        //输出PDF
        $this->pdf->Output('t.pdf', 'I');
    }

}