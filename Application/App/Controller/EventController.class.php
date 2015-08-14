<?php
/**
 * Created by PhpStorm.
 * User: andyYang
 * Date: 2015/8/14
 * Time: 17:12
 */

namespace App\Controller;


class EventController extends AppController{
    protected $eventModel;

    function _initialize(){
        $this->eventModel = D('Event/Event');
    }

    public function competitionList($page=1, $count=10){
        $picModel = D('Home/Picture');
        $listData = $this->eventModel->getEventList($page, $count);
        foreach($listData['list'] as &$event){
            $cover_id = $event['cover_id'];
            $result = $picModel->where('status=1 and id='.$cover_id)->field('path')->find();
            $event['pic_path'] = $result['path'];
        }
        $this->apiSuccess(json_encode($listData));
    }

}