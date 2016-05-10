<?php

namespace App\Controller;
use Addons\Avatar\AvatarAddon;
use Addons\Email\EmailUtils;
use Addons\Pdf\PdfUtils;
use Think\Hook;


class UniversityController extends AppController {

    /**
     * @param int $university_id
     */
    public function showUniversityMainPage($university_id=0){
        $this->assign('university_id', $university_id);
        $this->display('university_main_page');
    }

}