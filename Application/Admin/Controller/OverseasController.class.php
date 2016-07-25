<?php

namespace Admin\Controller;

use Think\Page;

/**
 * 留学模块
 * Class OverseasController
 * @package Admin\Controller
 */
class OverseasController extends AdminController
{

    public function index(){
        $model = M('AbroadCountry');
        $count = $model->where('status=1')->count();
        $Page = new Page($count, 10);
        $show = $Page->show();
        $list = $model->where('status=1')->order('create_time desc')->limit($Page->firstRow.','.$Page->listRows)->select();
        $this->assign('_list', $list);
        $this->assign('_page', $show);
        $this->assign("total", $count);
        $this->assign("meta_title","海外国家列表");
        $this->display();
    }

    public function country_add(){
        $this->display('country_add');
    }

    public function country_update(){
        if (IS_POST) { //提交表单
            $model = M('AbroadCountry');
            $cid = $_POST['cid'];
            $data['name'] = $_POST["name"];
            $pic_id = $_POST["logo_url"];
            if(!empty($pic_id)){
                A('Organization')->uploadLogoPicToOSS($pic_id);
                $data['logo_url'] = A('Organization')->fetchCdnImage($pic_id);
            }
            if(empty($cid)){
                $data["create_time"] = time();
                try {
                    $model->add($data);
                } catch (Exception $e) {
                    $this->error($e->getMessage());
                }
                $this->success('添加成功', 'index.php?s=/admin/overseas/index');
            } else {
                $model->where('id='.$cid)->save($data);
                $this->success('更新成功', 'index.php?s=/admin/overseas/index');
            }
        } else {
            $this->display('country_add');
        }
    }

    public function country_edit($id){
        if(empty($id)){
            $this->error('参数不能为空！');
        }
        /*获取一条记录的详细数据*/
        $Model = M('AbroadCountry');
        $data = $Model->where('id='.$id)->find();
        if(!$data){
            $this->error($Model->getError());
        }
        $this->assign('country', $data);
        $this->meta_title = '编辑留学国家';
        $this->display();
    }

    public function majors(){
        $model = M('AbroadUniversityMajors');
        $count = $model->count();
        $Page = new Page($count, 10);
        $show = $Page->show();
        $list = $model->order('create_time desc')->limit($Page->firstRow.','.$Page->listRows)->select();
        $this->assign('_list', $list);
        $this->assign('_page', $show);
        $this->assign("total", $count);
        $this->assign("meta_title","专业列表");
        $this->display();
    }

    public function majors_update(){
        if (IS_POST) { //提交表单
            $model = M('AbroadUniversityMajors');
            $mid = $_POST['mid'];
            $data['name'] = $_POST["name"];
            $data['type'] = $_POST["type"];
            if(empty($cid)){
                $data["create_time"] = time();
                try {
                    $model->add($data);
                } catch (Exception $e) {
                    $this->error($e->getMessage());
                }
                $this->success('添加成功', 'index.php?s=/admin/overseas/majors');
            } else {
                $model->where('id='.$cid)->save($data);
                $this->success('更新成功', 'index.php?s=/admin/overseas/majors');
            }
        } else {
            $this->display('majors_add');
        }
    }

    public function majors_edit($id){
        if(empty($id)){
            $this->error('参数不能为空！');
        }
        /*获取一条记录的详细数据*/
        $Model = M('AbroadUniversityMajors');
        $data = $Model->where('id='.$id)->find();
        if(!$data){
            $this->error($Model->getError());
        }
        $this->assign('majors', $data);
        $this->meta_title = '编辑专业';
        $this->display();
    }

    public function majors_set_status($id, $status=-1){
        if(!empty($id)){
            $model = M('AbroadUniversityMajors');
            $data['status'] = $status;
            if(is_array($id)){
                foreach ($id as $i){
                    $model->where('id='.$i)->save($data);
                }
            } else {
                $id = intval($id);
                $model->where('id='.$id)->save($data);
            }
            $this->success('处理成功','index.php?s=/admin/overseas/majors');
        } else {
            $this->error('未选择要处理的数据');
        }
    }

    public function cancle_hot($id, $is_hot=0){
        if(!empty($id)){
            $model = M('AbroadCountry');
            $data['is_hot'] = $is_hot;
            if(is_array($id)){
                foreach ($id as $i){
                    $model->where('id='.$i)->save($data);
                }
            } else {
                $id = intval($id);
                $model->where('id='.$id)->save($data);
            }
            $this->success('处理成功','index.php?s=/admin/overseas/index');
        } else {
            $this->error('未选择要处理的数据');
        }
    }

    public function country_set_hot($id){
        if(!empty($id)){
            $model = M('AbroadCountry');
            $data['is_hot'] = 1;
            $model->where('id='.$id)->save($data);
            $this->success('处理成功','index.php?s=/admin/overseas/index');
        } else {
            $this->error('未选择要处理的数据');
        }
    }

    public function country_set_status($id, $status=-1){
        if(!empty($id)){
            $model = M('AbroadCountry');
            $data['status'] = $status;
            if(is_array($id)){
                foreach ($id as $i){
                    $model->where('id='.$i)->save($data);
                }
            } else {
                $id = intval($id);
                $model->where('id='.$id)->save($data);
            }
            $this->success('处理成功','index.php?s=/admin/overseas/index');
        } else {
            $this->error('未选择要处理的数据');
        }
    }

    public function university(){
        $model = M('AbroadUniversity');
        $countryModel = M('AbroadCountry');
        $key_words = $_GET["key_words"];
        if($key_words){
            $map = "university.name like '%".$key_words."%' or country.name like '%".$key_words."%'";
            $map = $map . 'and university.country_id=country.id and university.status=1 and country.status=1';
            $list = $model->table('hisihi.hisihi_abroad_university university, hisihi.hisihi_abroad_country country')
                ->where($map)
                ->field('distinct(university.id), university.logo_url, university.country_id, university.name, university.is_hot, university.status')
                ->order('university.create_time desc' )
                ->select();
            $count = $model->table('hisihi_abroad_university university, hisihi_abroad_country country')
                ->where($map)
                ->count();
            $Page = new Page($count, 10);
        }else{
            $count = $model->where('status=1')->count();
            $Page = new Page($count, 10);
            $list = $model->where('status=1')->order('create_time desc')->limit($Page->firstRow.','.$Page->listRows)->select();
        }
        foreach($list as &$university){
            $country_id = $university['country_id'];
            $country_info = $countryModel->field('name')->where('id='.$country_id)->find();
            $university['country'] = $country_info['name'];
        }
        $show = $Page->show();
        $this->assign('_list', $list);
        $this->assign('_page', $show);
        $this->assign("total", $count);
        $this->assign("meta_title", "海外大学列表");
        $this->display();
    }

    public function setHot($id){
        if(!empty($id)){
            $model = M('Organization');
            $data['is_hot'] = 1;
            if(is_array($id)){
                foreach ($id as $i)
                {
                    $model->where('id='.$i)->save($data);
                }
            } else {
                $id = intval($id);
                $model->where('id='.$id)->save($data);
            }

            $this->success('设置成功','index.php?s=/admin/overseas/org_list');
        } else {
            $this->error('未选择要操作的数据');
        }
    }

    /**留学机构列表
     * @param string $type
     */
    public function org_list($type='留学'){
        $map['value'] = $type;
        $map['status'] = 1;
        $type_id = M('OrganizationTag')->where($map)->getField('id');
        $model = M('Organization');
        $is_hot = I('is_hot');
        if($is_hot){
            $where_map['is_hot'] = 1;
        }
        $city_name = I('city');
        if(!empty($city_name)){
            if($city_name == '吉林'){//区分吉林省和吉林市
                $where_map['city'] = array('like', '% '.$city_name.'%');
            }else{
                $where_map['city'] = array('like', '%'.$city_name.'%');
            }
        }
        $where_map['status'] = 1;
        $where_map['type'] = $type_id;
        $count = $model->where($where_map)->count();
        $Page = new Page($count, 5);
        $show = $Page->show();
        //用于公司名称搜索
        $name = $_GET["title"];
        if($name){
            $where_map['name'] = array('like','%'.$name.'%');
            $list = $model->where($where_map)->order('sort asc, create_time desc')->limit($Page->firstRow.','.$Page->listRows)->select();
        }else{
            $list = $model->where($where_map)->order('sort asc, create_time desc')->limit($Page->firstRow.','.$Page->listRows)->select();
        }
        foreach($list as &$org){
            $has_admin = M('OrganizationAdmin')->where('status=1 and id='.$org['uid'])->count();
            if($has_admin){
                $org['has_admin'] = 1;
            }else{
                $org['has_admin'] = 0;
            }
            $org['type'] = M('OrganizationTag')->where('type=7 and status=1 and id='.$org['type'])->getField('value');
        }
        $major = M('OrganizationTag')->field('id, value')->where('type=8 and status>0')->select();
        $type = M('OrganizationTag')->field('id, value')->where('type=7 and status=1')->select();

        $city = json_decode('[{"city":"\u5317\u4eac","code":"101010100"},{"city":"\u5929\u6d25","code":"101030100"},{"city":"\u4e0a\u6d77","code":"101020100"},{"city":"\u77f3\u5bb6\u5e84","code":"101090101"},{"city":"\u5f20\u5bb6\u53e3","code":"101090301"},{"city":"\u627f\u5fb7","code":"101090402"},{"city":"\u5510\u5c71","code":"101090501"},{"city":"\u79e6\u7687\u5c9b","code":"101091101"},{"city":"\u6ca7\u5dde","code":"101090701"},{"city":"\u8861\u6c34","code":"101090801"},{"city":"\u90a2\u53f0","code":"101090901"},{"city":"\u90af\u90f8","code":"101091001"},{"city":"\u4fdd\u5b9a","code":"101090201"},{"city":"\u5eca\u574a","code":"101090601"},{"city":"\u90d1\u5dde","code":"101180101"},{"city":"\u65b0\u4e61","code":"101180301"},{"city":"\u8bb8\u660c","code":"101180401"},{"city":"\u5e73\u9876\u5c71","code":"101180501"},{"city":"\u4fe1\u9633","code":"101180601"},{"city":"\u5357\u9633","code":"101180701"},{"city":"\u5f00\u5c01","code":"101180801"},{"city":"\u6d1b\u9633","code":"101180901"},{"city":"\u5546\u4e18","code":"101181001"},{"city":"\u7126\u4f5c","code":"101181101"},{"city":"\u9e64\u58c1","code":"101181201"},{"city":"\u6fee\u9633","code":"101181301"},{"city":"\u5468\u53e3","code":"101181401"},{"city":"\u6f2f\u6cb3","code":"101181501"},{"city":"\u9a7b\u9a6c\u5e97","code":"101181601"},{"city":"\u4e09\u95e8\u5ce1","code":"101181701"},{"city":"\u6d4e\u6e90","code":"101181801"},{"city":"\u5b89\u9633","code":"101180201"},{"city":"\u5408\u80a5","code":"101220101"},{"city":"\u829c\u6e56","code":"101220301"},{"city":"\u6dee\u5357","code":"101220401"},{"city":"\u9a6c\u978d\u5c71","code":"101220501"},{"city":"\u5b89\u5e86","code":"101220601"},{"city":"\u5bbf\u5dde","code":"101220701"},{"city":"\u961c\u9633","code":"101220801"},{"city":"\u4eb3\u5dde","code":"101220901"},{"city":"\u9ec4\u5c71","code":"101221001"},{"city":"\u6ec1\u5dde","code":"101221101"},{"city":"\u6dee\u5317","code":"101221201"},{"city":"\u94dc\u9675","code":"101221301"},{"city":"\u5ba3\u57ce","code":"101221401"},{"city":"\u516d\u5b89","code":"101221501"},{"city":"\u5de2\u6e56","code":"101221601"},{"city":"\u6c60\u5dde","code":"101221701"},{"city":"\u868c\u57e0","code":"101220201"},{"city":"\u676d\u5dde","code":"101210101"},{"city":"\u821f\u5c71","code":"101211101"},{"city":"\u6e56\u5dde","code":"101210201"},{"city":"\u5609\u5174","code":"101210301"},{"city":"\u91d1\u534e","code":"101210901"},{"city":"\u7ecd\u5174","code":"101210501"},{"city":"\u53f0\u5dde","code":"101210601"},{"city":"\u6e29\u5dde","code":"101210701"},{"city":"\u4e3d\u6c34","code":"101210801"},{"city":"\u8862\u5dde","code":"101211001"},{"city":"\u5b81\u6ce2","code":"101210401"},{"city":"\u91cd\u5e86","code":"101040100"},{"city":"\u5408\u5ddd","code":"101040300"},{"city":"\u5357\u5ddd","code":"101040400"},{"city":"\u6c5f\u6d25","code":"101040500"},{"city":"\u4e07\u76db","code":"101040600"},{"city":"\u6e1d\u5317","code":"101040700"},{"city":"\u5317\u789a","code":"101040800"},{"city":"\u5df4\u5357","code":"101040900"},{"city":"\u957f\u5bff","code":"101041000"},{"city":"\u9ed4\u6c5f","code":"101041100"},{"city":"\u4e07\u5dde\u5929\u57ce","code":"101041200"},{"city":"\u4e07\u5dde\u9f99\u5b9d","code":"101041300"},{"city":"\u6daa\u9675","code":"101041400"},{"city":"\u5f00\u53bf","code":"101041500"},{"city":"\u57ce\u53e3","code":"101041600"},{"city":"\u4e91\u9633","code":"101041700"},{"city":"\u5deb\u6eaa","code":"101041800"},{"city":"\u5949\u8282","code":"101041900"},{"city":"\u5deb\u5c71","code":"101042000"},{"city":"\u6f7c\u5357","code":"101042100"},{"city":"\u57ab\u6c5f","code":"101042200"},{"city":"\u6881\u5e73","code":"101042300"},{"city":"\u5fe0\u53bf","code":"101042400"},{"city":"\u77f3\u67f1","code":"101042500"},{"city":"\u5927\u8db3","code":"101042600"},{"city":"\u8363\u660c","code":"101042700"},{"city":"\u94dc\u6881","code":"101042800"},{"city":"\u74a7\u5c71","code":"101042900"},{"city":"\u4e30\u90fd","code":"101043000"},{"city":"\u6b66\u9686","code":"101043100"},{"city":"\u5f6d\u6c34","code":"101043200"},{"city":"\u7da6\u6c5f","code":"101043300"},{"city":"\u9149\u9633","code":"101043400"},{"city":"\u79c0\u5c71","code":"101043600"},{"city":"\u6c99\u576a\u575d","code":"101043700"},{"city":"\u6c38\u5ddd","code":"101040200"},{"city":"\u798f\u5dde","code":"101230101"},{"city":"\u6cc9\u5dde","code":"101230501"},{"city":"\u6f33\u5dde","code":"101230601"},{"city":"\u9f99\u5ca9","code":"101230701"},{"city":"\u664b\u6c5f","code":"101230509"},{"city":"\u5357\u5e73","code":"101230901"},{"city":"\u53a6\u95e8","code":"101230201"},{"city":"\u5b81\u5fb7","code":"101230301"},{"city":"\u8386\u7530","code":"101230401"},{"city":"\u4e09\u660e","code":"101230801"},{"city":"\u5170\u5dde","code":"101160101"},{"city":"\u5e73\u51c9","code":"101160301"},{"city":"\u5e86\u9633","code":"101160401"},{"city":"\u6b66\u5a01","code":"101160501"},{"city":"\u91d1\u660c","code":"101160601"},{"city":"\u5609\u5cea\u5173","code":"101161401"},{"city":"\u9152\u6cc9","code":"101160801"},{"city":"\u5929\u6c34","code":"101160901"},{"city":"\u6b66\u90fd","code":"101161001"},{"city":"\u4e34\u590f","code":"101161101"},{"city":"\u5408\u4f5c","code":"101161201"},{"city":"\u767d\u94f6","code":"101161301"},{"city":"\u5b9a\u897f","code":"101160201"},{"city":"\u5f20\u6396","code":"101160701"},{"city":"\u5e7f\u5dde","code":"101280101"},{"city":"\u60e0\u5dde","code":"101280301"},{"city":"\u6885\u5dde","code":"101280401"},{"city":"\u6c55\u5934","code":"101280501"},{"city":"\u6df1\u5733","code":"101280601"},{"city":"\u73e0\u6d77","code":"101280701"},{"city":"\u4f5b\u5c71","code":"101280800"},{"city":"\u8087\u5e86","code":"101280901"},{"city":"\u6e5b\u6c5f","code":"101281001"},{"city":"\u6c5f\u95e8","code":"101281101"},{"city":"\u6cb3\u6e90","code":"101281201"},{"city":"\u6e05\u8fdc","code":"101281301"},{"city":"\u4e91\u6d6e","code":"101281401"},{"city":"\u6f6e\u5dde","code":"101281501"},{"city":"\u4e1c\u839e","code":"101281601"},{"city":"\u4e2d\u5c71","code":"101281701"},{"city":"\u9633\u6c5f","code":"101281801"},{"city":"\u63ed\u9633","code":"101281901"},{"city":"\u8302\u540d","code":"101282001"},{"city":"\u6c55\u5c3e","code":"101282101"},{"city":"\u97f6\u5173","code":"101280201"},{"city":"\u5357\u5b81","code":"101300101"},{"city":"\u67f3\u5dde","code":"101300301"},{"city":"\u6765\u5bbe","code":"101300401"},{"city":"\u6842\u6797","code":"101300501"},{"city":"\u68a7\u5dde","code":"101300601"},{"city":"\u9632\u57ce\u6e2f","code":"101301401"},{"city":"\u8d35\u6e2f","code":"101300801"},{"city":"\u7389\u6797","code":"101300901"},{"city":"\u767e\u8272","code":"101301001"},{"city":"\u94a6\u5dde","code":"101301101"},{"city":"\u6cb3\u6c60","code":"101301201"},{"city":"\u5317\u6d77","code":"101301301"},{"city":"\u5d07\u5de6","code":"101300201"},{"city":"\u8d3a\u5dde","code":"101300701"},{"city":"\u8d35\u9633","code":"101260101"},{"city":"\u5b89\u987a","code":"101260301"},{"city":"\u90fd\u5300","code":"101260401"},{"city":"\u5174\u4e49","code":"101260906"},{"city":"\u94dc\u4ec1","code":"101260601"},{"city":"\u6bd5\u8282","code":"101260701"},{"city":"\u516d\u76d8\u6c34","code":"101260801"},{"city":"\u9075\u4e49","code":"101260201"},{"city":"\u51ef\u91cc","code":"101260501"},{"city":"\u6606\u660e","code":"101290101"},{"city":"\u7ea2\u6cb3","code":"101290301"},{"city":"\u6587\u5c71","code":"101290601"},{"city":"\u7389\u6eaa","code":"101290701"},{"city":"\u695a\u96c4","code":"101290801"},{"city":"\u666e\u6d31","code":"101290901"},{"city":"\u662d\u901a","code":"101291001"},{"city":"\u4e34\u6ca7","code":"101291101"},{"city":"\u6012\u6c5f","code":"101291201"},{"city":"\u9999\u683c\u91cc\u62c9","code":"101291301"},{"city":"\u4e3d\u6c5f","code":"101291401"},{"city":"\u5fb7\u5b8f","code":"101291501"},{"city":"\u666f\u6d2a","code":"101291601"},{"city":"\u5927\u7406","code":"101290201"},{"city":"\u66f2\u9756","code":"101290401"},{"city":"\u4fdd\u5c71","code":"101290501"},{"city":"\u547c\u548c\u6d69\u7279","code":"101080101"},{"city":"\u4e4c\u6d77","code":"101080301"},{"city":"\u96c6\u5b81","code":"101080401"},{"city":"\u901a\u8fbd","code":"101080501"},{"city":"\u963f\u62c9\u5584\u5de6\u65d7","code":"101081201"},{"city":"\u9102\u5c14\u591a\u65af","code":"101080701"},{"city":"\u4e34\u6cb3","code":"101080801"},{"city":"\u9521\u6797\u6d69\u7279","code":"101080901"},{"city":"\u547c\u4f26\u8d1d\u5c14","code":"101081000"},{"city":"\u4e4c\u5170\u6d69\u7279","code":"101081101"},{"city":"\u5305\u5934","code":"101080201"},{"city":"\u8d64\u5cf0","code":"101080601"},{"city":"\u5357\u660c","code":"101240101"},{"city":"\u4e0a\u9976","code":"101240301"},{"city":"\u629a\u5dde","code":"101240401"},{"city":"\u5b9c\u6625","code":"101240501"},{"city":"\u9e70\u6f6d","code":"101241101"},{"city":"\u8d63\u5dde","code":"101240701"},{"city":"\u666f\u5fb7\u9547","code":"101240801"},{"city":"\u840d\u4e61","code":"101240901"},{"city":"\u65b0\u4f59","code":"101241001"},{"city":"\u4e5d\u6c5f","code":"101240201"},{"city":"\u5409\u5b89","code":"101240601"},{"city":"\u6b66\u6c49","code":"101200101"},{"city":"\u9ec4\u5188","code":"101200501"},{"city":"\u8346\u5dde","code":"101200801"},{"city":"\u5b9c\u660c","code":"101200901"},{"city":"\u6069\u65bd","code":"101201001"},{"city":"\u5341\u5830","code":"101201101"},{"city":"\u795e\u519c\u67b6","code":"101201201"},{"city":"\u968f\u5dde","code":"101201301"},{"city":"\u8346\u95e8","code":"101201401"},{"city":"\u5929\u95e8","code":"101201501"},{"city":"\u4ed9\u6843","code":"101201601"},{"city":"\u6f5c\u6c5f","code":"101201701"},{"city":"\u8944\u6a0a","code":"101200201"},{"city":"\u9102\u5dde","code":"101200301"},{"city":"\u5b5d\u611f","code":"101200401"},{"city":"\u9ec4\u77f3","code":"101200601"},{"city":"\u54b8\u5b81","code":"101200701"},{"city":"\u6210\u90fd","code":"101270101"},{"city":"\u81ea\u8d21","code":"101270301"},{"city":"\u7ef5\u9633","code":"101270401"},{"city":"\u5357\u5145","code":"101270501"},{"city":"\u8fbe\u5dde","code":"101270601"},{"city":"\u9042\u5b81","code":"101270701"},{"city":"\u5e7f\u5b89","code":"101270801"},{"city":"\u5df4\u4e2d","code":"101270901"},{"city":"\u6cf8\u5dde","code":"101271001"},{"city":"\u5b9c\u5bbe","code":"101271101"},{"city":"\u5185\u6c5f","code":"101271201"},{"city":"\u8d44\u9633","code":"101271301"},{"city":"\u4e50\u5c71","code":"101271401"},{"city":"\u7709\u5c71","code":"101271501"},{"city":"\u51c9\u5c71","code":"101271601"},{"city":"\u96c5\u5b89","code":"101271701"},{"city":"\u7518\u5b5c","code":"101271801"},{"city":"\u963f\u575d","code":"101271901"},{"city":"\u5fb7\u9633","code":"101272001"},{"city":"\u5e7f\u5143","code":"101272101"},{"city":"\u6500\u679d\u82b1","code":"101270201"},{"city":"\u94f6\u5ddd","code":"101170101"},{"city":"\u4e2d\u536b","code":"101170501"},{"city":"\u56fa\u539f","code":"101170401"},{"city":"\u77f3\u5634\u5c71","code":"101170201"},{"city":"\u5434\u5fe0","code":"101170301"},{"city":"\u897f\u5b81","code":"101150101"},{"city":"\u9ec4\u5357","code":"101150301"},{"city":"\u6d77\u5317","code":"101150801"},{"city":"\u679c\u6d1b","code":"101150501"},{"city":"\u7389\u6811","code":"101150601"},{"city":"\u6d77\u897f","code":"101150701"},{"city":"\u6d77\u4e1c","code":"101150201"},{"city":"\u6d77\u5357","code":"101150401"},{"city":"\u6d4e\u5357","code":"101120101"},{"city":"\u6f4d\u574a","code":"101120601"},{"city":"\u4e34\u6c82","code":"101120901"},{"city":"\u83cf\u6cfd","code":"101121001"},{"city":"\u6ee8\u5dde","code":"101121101"},{"city":"\u4e1c\u8425","code":"101121201"},{"city":"\u5a01\u6d77","code":"101121301"},{"city":"\u67a3\u5e84","code":"101121401"},{"city":"\u65e5\u7167","code":"101121501"},{"city":"\u83b1\u829c","code":"101121601"},{"city":"\u804a\u57ce","code":"101121701"},{"city":"\u9752\u5c9b","code":"101120201"},{"city":"\u6dc4\u535a","code":"101120301"},{"city":"\u5fb7\u5dde","code":"101120401"},{"city":"\u70df\u53f0","code":"101120501"},{"city":"\u6d4e\u5b81","code":"101120701"},{"city":"\u6cf0\u5b89","code":"101120801"},{"city":"\u897f\u5b89","code":"101110101"},{"city":"\u5ef6\u5b89","code":"101110300"},{"city":"\u6986\u6797","code":"101110401"},{"city":"\u94dc\u5ddd","code":"101111001"},{"city":"\u5546\u6d1b","code":"101110601"},{"city":"\u5b89\u5eb7","code":"101110701"},{"city":"\u6c49\u4e2d","code":"101110801"},{"city":"\u5b9d\u9e21","code":"101110901"},{"city":"\u54b8\u9633","code":"101110200"},{"city":"\u6e2d\u5357","code":"101110501"},{"city":"\u592a\u539f","code":"101100101"},{"city":"\u4e34\u6c7e","code":"101100701"},{"city":"\u8fd0\u57ce","code":"101100801"},{"city":"\u6714\u5dde","code":"101100901"},{"city":"\u5ffb\u5dde","code":"101101001"},{"city":"\u957f\u6cbb","code":"101100501"},{"city":"\u5927\u540c","code":"101100201"},{"city":"\u9633\u6cc9","code":"101100301"},{"city":"\u664b\u4e2d","code":"101100401"},{"city":"\u664b\u57ce","code":"101100601"},{"city":"\u5415\u6881","code":"101101100"},{"city":"\u4e4c\u9c81\u6728\u9f50","code":"101130101"},{"city":"\u77f3\u6cb3\u5b50","code":"101130301"},{"city":"\u660c\u5409","code":"101130401"},{"city":"\u5410\u9c81\u756a","code":"101130501"},{"city":"\u5e93\u5c14\u52d2","code":"101130601"},{"city":"\u963f\u62c9\u5c14","code":"101130701"},{"city":"\u963f\u514b\u82cf","code":"101130801"},{"city":"\u5580\u4ec0","code":"101130901"},{"city":"\u4f0a\u5b81","code":"101131001"},{"city":"\u5854\u57ce","code":"101131101"},{"city":"\u54c8\u5bc6","code":"101131201"},{"city":"\u548c\u7530","code":"101131301"},{"city":"\u963f\u52d2\u6cf0","code":"101131401"},{"city":"\u963f\u56fe\u4ec0","code":"101131501"},{"city":"\u535a\u4e50","code":"101131601"},{"city":"\u514b\u62c9\u739b\u4f9d","code":"101130201"},{"city":"\u62c9\u8428","code":"101140101"},{"city":"\u5c71\u5357","code":"101140301"},{"city":"\u963f\u91cc","code":"101140701"},{"city":"\u660c\u90fd","code":"101140501"},{"city":"\u90a3\u66f2","code":"101140601"},{"city":"\u65e5\u5580\u5219","code":"101140201"},{"city":"\u6797\u829d","code":"101140401"},{"city":"\u53f0\u5317\u53bf","code":"101340101"},{"city":"\u9ad8\u96c4","code":"101340201"},{"city":"\u53f0\u4e2d","code":"101340401"},{"city":"\u6d77\u53e3","code":"101310101"},{"city":"\u4e09\u4e9a","code":"101310201"},{"city":"\u4e1c\u65b9","code":"101310202"},{"city":"\u4e34\u9ad8","code":"101310203"},{"city":"\u6f84\u8fc8","code":"101310204"},{"city":"\u510b\u5dde","code":"101310205"},{"city":"\u660c\u6c5f","code":"101310206"},{"city":"\u767d\u6c99","code":"101310207"},{"city":"\u743c\u4e2d","code":"101310208"},{"city":"\u5b9a\u5b89","code":"101310209"},{"city":"\u5c6f\u660c","code":"101310210"},{"city":"\u743c\u6d77","code":"101310211"},{"city":"\u6587\u660c","code":"101310212"},{"city":"\u4fdd\u4ead","code":"101310214"},{"city":"\u4e07\u5b81","code":"101310215"},{"city":"\u9675\u6c34","code":"101310216"},{"city":"\u897f\u6c99","code":"101310217"},{"city":"\u5357\u6c99\u5c9b","code":"101310220"},{"city":"\u4e50\u4e1c","code":"101310221"},{"city":"\u4e94\u6307\u5c71","code":"101310222"},{"city":"\u743c\u5c71","code":"101310102"},{"city":"\u957f\u6c99","code":"101250101"},{"city":"\u682a\u6d32","code":"101250301"},{"city":"\u8861\u9633","code":"101250401"},{"city":"\u90f4\u5dde","code":"101250501"},{"city":"\u5e38\u5fb7","code":"101250601"},{"city":"\u76ca\u9633","code":"101250700"},{"city":"\u5a04\u5e95","code":"101250801"},{"city":"\u90b5\u9633","code":"101250901"},{"city":"\u5cb3\u9633","code":"101251001"},{"city":"\u5f20\u5bb6\u754c","code":"101251101"},{"city":"\u6000\u5316","code":"101251201"},{"city":"\u9ed4\u9633","code":"101251301"},{"city":"\u6c38\u5dde","code":"101251401"},{"city":"\u5409\u9996","code":"101251501"},{"city":"\u6e58\u6f6d","code":"101250201"},{"city":"\u5357\u4eac","code":"101190101"},{"city":"\u9547\u6c5f","code":"101190301"},{"city":"\u82cf\u5dde","code":"101190401"},{"city":"\u5357\u901a","code":"101190501"},{"city":"\u626c\u5dde","code":"101190601"},{"city":"\u5bbf\u8fc1","code":"101191301"},{"city":"\u5f90\u5dde","code":"101190801"},{"city":"\u6dee\u5b89","code":"101190901"},{"city":"\u8fde\u4e91\u6e2f","code":"101191001"},{"city":"\u5e38\u5dde","code":"101191101"},{"city":"\u6cf0\u5dde","code":"101191201"},{"city":"\u65e0\u9521","code":"101190201"},{"city":"\u76d0\u57ce","code":"101190701"},{"city":"\u54c8\u5c14\u6ee8","code":"101050101"},{"city":"\u7261\u4e39\u6c5f","code":"101050301"},{"city":"\u4f73\u6728\u65af","code":"101050401"},{"city":"\u7ee5\u5316","code":"101050501"},{"city":"\u9ed1\u6cb3","code":"101050601"},{"city":"\u53cc\u9e2d\u5c71","code":"101051301"},{"city":"\u4f0a\u6625","code":"101050801"},{"city":"\u5927\u5e86","code":"101050901"},{"city":"\u4e03\u53f0\u6cb3","code":"101051002"},{"city":"\u9e21\u897f","code":"101051101"},{"city":"\u9e64\u5c97","code":"101051201"},{"city":"\u9f50\u9f50\u54c8\u5c14","code":"101050201"},{"city":"\u5927\u5174\u5b89\u5cad","code":"101050701"},{"city":"\u957f\u6625","code":"101060101"},{"city":"\u5ef6\u5409","code":"101060301"},{"city":"\u56db\u5e73","code":"101060401"},{"city":"\u767d\u5c71","code":"101060901"},{"city":"\u767d\u57ce","code":"101060601"},{"city":"\u8fbd\u6e90","code":"101060701"},{"city":"\u677e\u539f","code":"101060801"},{"city":"\u5409\u6797","code":"101060201"},{"city":"\u901a\u5316","code":"101060501"},{"city":"\u6c88\u9633","code":"101070101"},{"city":"\u978d\u5c71","code":"101070301"},{"city":"\u629a\u987a","code":"101070401"},{"city":"\u672c\u6eaa","code":"101070501"},{"city":"\u4e39\u4e1c","code":"101070601"},{"city":"\u846b\u82a6\u5c9b","code":"101071401"},{"city":"\u8425\u53e3","code":"101070801"},{"city":"\u961c\u65b0","code":"101070901"},{"city":"\u8fbd\u9633","code":"101071001"},{"city":"\u94c1\u5cad","code":"101071101"},{"city":"\u671d\u9633","code":"101071201"},{"city":"\u76d8\u9526","code":"101071301"},{"city":"\u5927\u8fde","code":"101070201"},{"city":"\u9526\u5dde","code":"101070701"}]
', true);
        $this->assign('city_name', $city_name);
        $this->assign('city', $city);
        $this->assign('is_hot', I('is_hot'));
        $this->assign('type', $type);
        $this->assign('major', $major);
        $this->assign('_list', $list);
        $this->assign('_page', $show);
        $this->assign("_total", $count);
        $this->assign("meta_title","机构列表");
        $this->display();
    }

    public function university_set_status($id, $status=-1)
    {
        if (!empty($id)) {
            $model = M('AbroadUniversity');
            $data['status'] = $status;
            if (is_array($id)) {
                foreach ($id as $i) {
                    $model->where('id=' . $i)->save($data);
                }
            } else {
                $id = intval($id);
                $model->where('id=' . $id)->save($data);
            }
            $this->success('处理成功', 'index.php?s=/admin/overseas/university');
        } else {
                $this->error('未选择要处理的数据');
        }
    }

    public function university_update(){
        if (IS_POST) { //提交表单
            $model = M('AbroadUniversity');
            $uid = $_POST['uid'];
            $data['name'] = $_POST["name"];
            $data['website'] = $_POST["website"];
            $data['introduction'] = $_POST["introduction"];
            $data['sia_recommend_level'] = $_POST["sia_recommend_level"];
            $data['sia_student_enrollment_rate'] = $_POST["sia_student_enrollment_rate"];
            $data['difficulty_of_application'] = $_POST["difficulty_of_application"];
            $data['tuition_fees'] = $_POST["tuition_fees"];
            $data['toefl'] = $_POST["toefl"];
            $data['ielts'] = $_POST["ielts"];
            $data['proportion_of_undergraduates'] = $_POST["proportion_of_undergraduates"];
            $data['scholarship'] = $_POST["scholarship"];
            $data['deadline_for_applications'] = $_POST["deadline_for_applications"];
            $data['application_requirements'] = $_POST["application_requirements"];
            $data['school_environment'] = $_POST["school_environment"];
            $data['country_id'] = $_POST["country_id"];
            $data['undergraduate_majors'] = $_POST["undergraduate_majors"];
            $data['graduate_majors'] = $_POST["graduate_majors"];
            $pic_id = $_POST["logo_url"];
            if(!empty($pic_id)){
                A('Organization')->uploadLogoPicToOSS($pic_id);
                $data['logo_url'] = A('Organization')->fetchCdnImage($pic_id);
            }
            if(empty($uid)){
                $data["create_time"] = time();
                try {
                    $model->add($data);
                } catch (Exception $e) {
                    $this->error($e->getMessage());
                }
                $this->success('添加成功', 'index.php?s=/admin/overseas/university');
            } else {
                $model->where('id='.$uid)->save($data);
                $this->success('更新成功', 'index.php?s=/admin/overseas/university');
            }
        } else {
            $this->display('university_add');
        }
    }

    public function university_add(){
        $majorModel = M('AbroadUniversityMajors');
        $countryModel = M('AbroadCountry');
        $country_list = $countryModel->field('id, name')->where('status=1')->select();
        $undergraduate_majors = $majorModel->field('id, name')->where('status=1 and type=1')->select();
        $graduate_majors = $majorModel->field('id, name')->where('status=1 and type=2')->select();
        $this->assign('_country', $country_list);
        $this->assign('_undergraduate_majors', $undergraduate_majors);
        $this->assign('_graduate_majors', $graduate_majors);
        $this->display('university_add');
    }

    public function university_set_hot($id){
        if(!empty($id)){
            $model = M('AbroadUniversity');
            $data['is_hot'] = 1;
            $model->where('id='.$id)->save($data);
            $this->success('处理成功','index.php?s=/admin/overseas/university');
        } else {
            $this->error('未选择要处理的数据');
        }
    }

    public function cancle_university_hot($id, $is_hot=0)
    {
        if (!empty($id)) {
            $model = M('AbroadUniversity');
            $data['is_hot'] = $is_hot;
            if (is_array($id)) {
                foreach ($id as $i) {
                    $model->where('id=' . $i)->save($data);
                }
            } else {
                $id = intval($id);
                $model->where('id=' . $id)->save($data);
            }
            $this->success('处理成功', 'index.php?s=/admin/overseas/university');
        }
        $this->error('未选择要处理的数据');
    }

    /**
     * @param $id
     */
    public function undoSetHot($id){
        if(!empty($id)){
            $model = M('Organization');
            $data['is_hot'] = 0;
            if(is_array($id)){
                foreach ($id as $i)
                {
                    $model->where('id='.$i)->save($data);
                }
            } else {
                $id = intval($id);
                $model->where('id='.$id)->save($data);
            }
            $this->success('取消成功','index.php?s=/admin/overseas/org_list');
        } else {
            $this->error('未选择要操作的数据');
        }
    }

    public function university_edit($id){
        if(empty($id)){
            $this->error('参数不能为空！');
        }
        /*获取一条记录的详细数据*/
        $Model = M('AbroadUniversity');
        $data = $Model->where('id='.$id)->find();
        if(!$data){
            $this->error($Model->getError());
        }
        $majorModel = M('AbroadUniversityMajors');
        $undergraduate_majors_list = $majorModel->field('id, name')->where('status=1 and type=1')->select();
        $graduate_majors_list = $majorModel->field('id, name')->where('status=1 and type=2')->select();
        $undergraduate_majors = explode("#",$data['undergraduate_majors']);
        $graduate_majors = explode("#",$data['graduate_majors']);
        foreach($undergraduate_majors_list as &$all_major){
            $is_exist = false;
            if(in_array($all_major['id'], $undergraduate_majors)){
                $is_exist = true;
            }
            if(!$is_exist){
                $all_major['ischecked'] = 0;
            }else{
                $all_major['ischecked'] = 1;
            }
        }
        foreach($graduate_majors_list as &$all_major){
            $is_exist = false;
            if(in_array($all_major['id'], $graduate_majors)){
                $is_exist = true;
            }
            if(!$is_exist){
                $all_major['ischecked'] = 0;
            }else{
                $all_major['ischecked'] = 1;
            }
        }
        $countryModel = M('AbroadCountry');
        $country_list = $countryModel->field('id, name')->where('status=1')->select();
        $this->assign('_country', $country_list);
        $this->assign('_undergraduate_majors', $undergraduate_majors_list);
        $this->assign('_graduate_majors', $graduate_majors_list);
        $this->assign('university', $data);
        $this->meta_title = '编辑大学';
        $this->display();
    }

    public function photo(){
        $model = M('AbroadUniversity');
        $photoModel = M('AbroadUniversityPhotos');
        $count = $photoModel->where('status=1')->count();
        $Page = new Page($count, 10);
        $show = $Page->show();
        $list = $photoModel->where('status=1')->order('create_time desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        foreach($list as &$photo){
            $university_id = $photo['university_id'];
            $university_info = $model->field('name')->where('id='.$university_id)->find();
            $photo['university'] = $university_info['name'];
        }
        $university_id = I('university_id');
        $university_name = I('university_name');
        if($university_id){
            $this->assign('university_id', $university_id);
            $this->assign('university_name', $university_name);
        }
        $this->assign('_list', $list);
        $this->assign('_page', $show);
        $this->assign("total", $count);
        $this->assign("meta_title", "大学相册列表");
        $this->display();
    }

    public function photo_add(){
        if(I('university_id')){
            $this->assign('university_id', I('university_id'));
            $this->assign('university_name', I('university_name'));
        }

        $this->display();
    }

    public function photo_edit($id){
        if(I('university_id')){
            $this->assign('university_id', I('university_id'));
            $this->assign('university_name', I('university_name'));
        }
        $photo = M('AbroadUniversityPhotos')->where('id='.$id)->find();
        $this->assign('info', $photo);
        $this->display();
    }

    public function photo_update(){
        if (IS_POST) { //提交表单
            $model = M('AbroadUniversityPhotos');
            $uid = $_POST['pid'];
            $university_name = $_POST["university_name"];
            $data['descript'] = $_POST["descript"];
            $data['university_id'] = $_POST["university_id"];
            $pic_id = $_POST["pic_url"];
            if(is_numeric($pic_id)){
                A('Organization')->uploadLogoPicToOSS($pic_id);
                $data['pic_url'] = A('Organization')->fetchCdnImage($pic_id);
            }else{
                $data['pic_url'] = $pic_id;
            }
            if(empty($uid)){
                $data["create_time"] = time();
                try {
                    $model->add($data);
                } catch (Exception $e) {
                    $this->error($e->getMessage());
                }
                $this->success('添加成功', 'index.php?s=/admin/overseas/photo&university_id='.$data['university_id'].'&university_name='.$university_name);
            } else {
                $model->where('id='.$uid)->save($data);
                $this->success('更新成功', 'index.php?s=/admin/overseas/photo&university_id='.$data['university_id'].'&university_name='.$university_name);
            }
        } else {
            $this->display('photo_add');
        }
    }

    public function photo_set_status($id, $status=-1)
    {
        if (!empty($id)) {
            $model = M('AbroadUniversityPhotos');
            $data['status'] = $status;
            if (is_array($id)) {
                foreach ($id as $i) {
                    $model->where('id=' . $i)->save($data);
                }
            } else {
                $id = intval($id);
                $model->where('id=' . $id)->save($data);
            }
            $this->success('处理成功', 'index.php?s=/admin/overseas/photo');
        } else {
            $this->error('未选择要处理的数据');
        }
    }

    public function enroll(){
        $keywords = I('title');
        $map = array();
        if($keywords){
            $map['student_name'] = array('like', '%'.$keywords.'%');
            $map['student_phone_num'] = array('like', '%'.$keywords.'%');
            $map['_logic'] = 'OR';
        }
        $model = M('AbroadUniversity');
        $majorModel = M('OrganizationUniversityEnroll');
        $count = $majorModel->where($map)->count();
        $Page = new Page($count, 10);
        $show = $Page->show();
        $list = $majorModel->where($map)->order('create_time desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        foreach($list as &$major){
            $university_id = $major['university_id'];
            $university_info = $model->field('name')->where('id='.$university_id)->find();
            $major['university'] = $university_info['name'];
        }
        $university_id = I('university_id');
        $university_name = I('university_name');
        if($university_id){
            $this->assign('university_id', $university_id);
            $this->assign('university_name', $university_name);
        }
        $this->assign('_list', $list);
        $this->assign('_page', $show);
        $this->assign("total", $count);
        $this->assign("meta_title", "大学报名列表");
        $this->display();
    }

    public function enroll_set_status($id, $status=-1)
    {
        if (!empty($id)) {
            $model = M('OrganizationUniversityEnroll');
            $data['status'] = $status;
            if (is_array($id)) {
                foreach ($id as $i) {
                    $model->where('id=' . $i)->save($data);
                }
            } else {
                $id = intval($id);
                $model->where('id=' . $id)->save($data);
            }
            $this->success('处理成功', 'index.php?s=/admin/overseas/enroll');
        } else {
            $this->error('未选择要处理的数据');
        }
    }

    public function plan(){
        $org_id = I('org_id');
        if($org_id){
            $map['organization_id'] = $org_id;
        }
        $map['status'] = 1;

        $model = M('Organization');
        $planModel = M('OverseasPlan');
        $count = $planModel->where($map)->count();
        $Page = new Page($count, 10);
        $show = $Page->show();
        $list = $planModel->where($map)->order('create_time desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
        foreach($list as &$plan){
            $organization_id = $plan['organization_id'];
            $org_info = $model->field('name')->where('id='.$organization_id)->find();
            $plan['organization'] = $org_info['name'];
        }
        $this->assign('_list', $list);
        $this->assign('_page', $show);
        $this->assign("total", $count);
        $this->assign("meta_title", "留学计划列表");
        $this->display();
    }

    public function plan_set_status($id, $status=-1)
    {
        if (!empty($id)) {
            $model = M('OverseasPlan');
            $data['status'] = $status;
            if (is_array($id)) {
                foreach ($id as $i) {
                    $model->where('id=' . $i)->save($data);
                }
            } else {
                $id = intval($id);
                $model->where('id=' . $id)->save($data);
            }
            $this->success('处理成功', 'index.php?s=/admin/overseas/plan');
        } else {
            $this->error('未选择要处理的数据');
        }
    }

    public function setOrgSort($id, $sort=100){
        if(!empty($id)){
            $model = M('Organization');
            $data['sort'] = $sort;
            $id = intval($id);
            $model->where('id='.$id)->save($data);
            if(I('type')){
                $this->success('设置成功','index.php?s=/admin/organization/searchtype&type='.I('type'));
            }
            if(I('major')){
                $this->success('设置成功','index.php?s=/admin/organization/searchmajor&major='.I('major'));
            }
            if(I('is_hot')){
                $this->success('设置成功','index.php?s=/admin/overseas/org_list&is_hot=1');
            }
            $this->success('设置成功','index.php?s=/admin/overseas/org_list');
        } else {
            $this->error('未选择要处理的数据');
        }
    }
}
