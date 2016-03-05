<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Addons\Advs\Controller;
use Admin\Controller\AddonsController;
use Think\Hook;

class AdvsController extends AddonsController{
	/* 添加 */
	public function add(){
		$current = U('/Admin/Addons/adminList/name/Advs');
		$sing = M('advertising')->where('status = 1')->select();
        $this->assign('meta_title', '新增广告');
		$this->assign('current',$current);
		$this->assign('sing',$sing);
		$this->display(T('Addons://Advs@Advs/edit'));
	}
	
	/* 编辑 */
	public function edit(){
		$id     =   I('get.id','');
		$current = U('/Admin/Addons/adminList/name/Advs');
		$sing = M('advertising')->where('status = 1')->select();
		$detail = D('Addons://Advs/Advs')->detail($id);
        $this->assign('meta_title', '广告编辑');
		$this->assign('info',$detail);
		$this->assign('current',$current);
		$this->assign('sing',$sing);
		$this->display(T('Addons://Advs@Advs/edit'));
	}
	
	/* 禁用 */
	public function forbidden(){
		$id     =   I('get.id','');
		if(D('Addons://Advs/Advs')->forbidden($id)){
			$this->success('成功禁用该广告', Cookie('__forward__'));
		}else{
			$this->error(D('Addons://Advs/Advs')->getError());
		}
	}
	
	/* 启用 */
	public function off(){
		$id     =   I('get.id','');
		if(D('Addons://Advs/Advs')->off($id)){
			$this->success('成功启用该广告',Cookie('__forward__'));
		}else{
			$this->error(D('Addons://Advs/Advs')->getError());
		}
	}
	
	/* 删除 */
	public function del(){
		$id     =   I('get.id','');
		if(D('Addons://Advs/Advs')->del($id)){
			$this->success('删除成功', Cookie('__forward__'));
		}else{
			$this->error(D('Addons://Advs/Advs')->getError());
		}
	}
	
	/* 更新 */
	public function update(){
		$res = D('Addons://Advs/Advs')->update();
		if(!$res){
			$this->error(D('Addons://Advs/Advs')->getError());
		}else{
            $this->uploadAdvsPicToOSS($res['advspic_640_960']);
            $this->uploadAdvsPicToOSS($res['advspic_720_1280']);
            $this->uploadAdvsPicToOSS($res['advspic_1242_2208']);
			if($res['id']){
				$this->success('更新成功', Cookie('__forward__'));
			}else{
				$this->success('新增成功', Cookie('__forward__'));
			}
		}
	}

    /*
     * 上传广告图片到OSS
     */
    private function uploadAdvsPicToOSS($advPicID){
        $model = M();
        $result = $model->query("select path from hisihi_picture where id=".$advPicID);
        if($result){
            $picLocalPath = $result[0]['path'];
            $picKey = substr($picLocalPath, 17);
            $param["bucketName"] = "advs-pic";
            $param['objectKey'] = $picKey;
            $isExist = Hook::exec('Addons\\Aliyun_Oss\\Aliyun_OssAddon', 'isResourceExistInOSS', $param);
            if(!$isExist){
                Hook::exec('Addons\\Aliyun_Oss\\Aliyun_OssAddon', 'uploadAdvsPicResource', $param);
            }
        }
    }
	/**
	 * 批量处理
	 */
	public function savestatus(){
		$status = I('get.status');
		$ids = I('post.id');
		
		if($status == 1){
			foreach ($ids as $id)
			{
				D('Addons://Advs/Advs')->off($id);
			}
			$this->success('成功启用该广告',Cookie('__forward__'));
		}else{
			foreach ($ids as $id)
			{
				D('Addons://Advs/Advs')->forbidden($id);
			}
			$this->success('成功禁用该广告',Cookie('__forward__'));
		}
	}

	/**
	 * 添加广告到首页资讯流内容当中
	 */
	public function addToInformationFlowContent(){
		$ids = I('post.id');
		if(empty($ids)){
			$this->error('请选择要操作的数据');
		}
		$model = M('InformationFlowContent');
		try {
			foreach($ids as $rid){
				$content_detail = $model->where('content_type=3 and content_id='.$rid)->count();
				if($content_detail){
					if($content_detail['status']==-1){
						$model->where('content_type=3 and content_id='.$rid)->save(array('status'=>1));
					}
				} else {
					$advs_model = D('Advs');
					$advs_detail = $advs_model->where(array('id'=>$rid, 'status'=>1))->find();
					$name = $advs_detail['title'];
					$data['content_id'] = $rid;
					$data['content_type'] = 3;
					$data['content_name'] = $name;
					$data['create_time'] = time();
					$model->add($data);
				}
			}
		} catch (Exception $e){
			$this->error('添加失败，请检查后重试');
		}
		$this->success("添加成功", 'index.php?s=/admin/information_flow/content');
	}

}
