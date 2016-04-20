<?php
/**
 * Created by PhpStorm.
 * Author: RFly
 * Date: 2/4/15
 * Time: 4:20 PM
 */

namespace App\Controller;
use Addons\Avatar\AvatarAddon;
//use Addons\Digg\DiggAddon;
//use Addons\Favorite\FavoriteAddon;
//use Addons\LocalComment\LocalCommentAddon;

class DocumentController extends AppController {
    public function viewDocument($document_id, $comment_count=10) {
        //读取文章的详细信息
        $document = $this->getTopicStructure($document_id, $comment_count);
        if(!$document) {
            $this->apiError(2001,"文章不存在");
        }
        //增加浏览次数
        $map = array('id'=>$document_id);
        $data = array('view'=>$document['view_count']+1);
        $model = D('Home/Document');
        $model->where($map)->save($data);
        //返回成功
        $this->apiSuccess("获取成功", null, array('document'=>$document));
    }

    public function diggDocument($document_id) {
        $this->requireLogin();
        //调用赞插件增加赞数量
        $addon = new DiggAddon();
        $digg_count = $addon->vote($this->getUid(), $document_id);
        if($digg_count === false) {
            $this->apiError(2101,"您已经赞过，不能重复赞");
        }
        //返回成功消息
        $this->apiSuccess("操作成功", null, array('digg_count'=>$digg_count));
    }

    public function newFavorite($document_id) {
        $this->requireLogin();
        //将文章添加到收藏夹
        $addon = new FavoriteAddon;
        $model = $addon->getFavoriteModel();
        $result = $model->addFavorite($this->getUid(), $document_id);
        if(!$result) {
            $this->errorCode = 2201;
            $this->error = "收藏失败：".$model->getError();
            return false;
        }
        //增加文章收藏数
        $document = D('Home/Document')->detail($document_id);
        $model_name = D('Admin/Model')->getNameById($document['model_id']);
        if($model_name == 'weibo') {
            D('Home/Weibo','Logic')->where(array('id'=>$document['id']))->save(array('bookmark'=>$document['bookmark']+1));
        }
        //返回收藏编号
        $this->apiSuccess("收藏成功", null, array('favorite_id'=>$result));
    }

    public function newComment($document_id, $content) {
        $this->requireLogin();
        //调用评论插件写入数据库
        $addon = new LocalCommentAddon;
        $model = $addon->getCommentModel();
        $comment_id = $model->addComment($this->getUid(), $document_id, $content);
        if(!$comment_id) {
            $this->apiError(2301,"评论失败");
        }
        //返回成功消息
        $this->apiSuccess("评论成功", null, array('comment_id'=>$comment_id));
    }

    public function listComment($document_id, $offset=2, $count=10) {
        $result = $this->getCommentList($document_id, $offset, $count);
        $totalCount = $this->getCommentCount($document_id);
        $this->apiSuccess("获取成功", null, array('list'=>$result,'total_count'=>$totalCount));
    }

    public function newDocument($content) {
        $this->requireLogin();
        //获取文档默认分类
        $category = M('category')->where(array('name'=>'default_blog','status'=>1))->find();
        $categoryId = $category['id'];
        if(!$categoryId) {
            $this->apiError(0,'找不到默认的文章分类');
        }
        //新建基础文档
        $model_id = D('Admin/Model')->getIdByName('weibo');
        if(!$model_id) {
            $this->apiError(2401,'找不到微博模型');
        }
        $row = array(
            'uid'=>$this->getUid(),
            'name'=>'',
            'title'=>'微博',
            'category_id'=>$categoryId,
            'description'=>'',
            'root'=>0,
            'pid'=>0,
            'model_id'=>$model_id,
            'type'=>2,
            'position'=>0,
            'link_id'=>0,
            'cover_id'=>0,
            'display'=>1,
            'deadline'=>0,
            'attach'=>0,
            'view'=>0,
            'comment'=>0,
            'extend'=>0,
            'level'=>0,
            'create_time'=>time(),
            'update_time'=>time(),
            'status'=>1
        );
        $model = D('Home/Document');
        $document_id = $model->add($row);
        //新建扩展文档
        $row = array(
            'id'=>$document_id,
            'parse'=>0,
            'content'=>$content,
            'bookmark'=>0,
        );
        $model = D('Home/Weibo','Logic');
        $model->add($row);
        //返回结果
        $this->apiSuccess("发表成功", null, array('document_id'=>$document_id));
    }

    public function editDocument($document_id, $content) {
        $this->requireLogin();
        //确认有权限编辑文档
        $document = D('Home/Document')->detail($document_id);
        if($document['uid'] != $this->getUid()) {
            $this->apiError(2501,'您没有编辑权限');
            return false;
        }
        //更新基础文档
        $row['update_time'] = time();
        D('Home/Document')->where(array('id'=>$document_id))->save($row);
        //更新扩展文档
        D('Home/Weibo','Logic')->where(array('id'=>$document_id))->save(array('content'=>$content));
        //返回成功信息
        $this->apiSuccess("编辑成功");
    }

    /**
     * 设计头条点赞
     * @param $id
     */
    public function doSupport($id){
        $this->requireLogin();
        $support['appname'] = 'Article';
        $support['table'] = 'article_content';
        $support['row'] = $id;
        $support['uid'] = is_login();
        if (D('Support')->where($support)->count()) {
            $this->apiError(-100,'您已经赞过，不能再赞了!');
        } else {
            $support['create_time'] = time();
            if (D('Support')->where($support)->add($support)) {
                unset($support['create_time']);
                M('Oppose')->where($support)->delete();//点赞时取消踩
                $this->clearCache($support, 'oppose');
                $this->clearCache($support);
                $this->apiSuccess('感谢您的支持');
            } else {
                $this->apiError(-101,'写入数据库失败!');
            }
        }
    }

    /**
     * 取消设计头条点赞
     * @param $id
     */
    public function unDoSupport($id)
    {
        $this->requireLogin();
        $support['appname'] = 'Article';
        $support['table'] = 'article_content';
        $support['row'] = $id;
        $support['uid'] = is_login();
        if (D('Support')->where($support)->count()) {
            if (D('Support')->where($support)->delete()) {
                $this->clearCache($support);
                $this->apiSuccess('取消点赞成功！');
            } else {
                $this->apiError(-101,'写入数据库失败!');
            }
        } else {
            $this->apiError(-102,'您还没有赞过，不能取消!');
        }
    }

    /**
     * 设计头条收藏
     * @param $id
     */
    public function doFavorite($id)
    {
        $this->requireLogin();
        $favorite['appname'] = 'Article';
        $favorite['table'] = 'article_content';
        $favorite['row'] = $id;
        $favorite['uid'] = is_login();
        if (D('Favorite')->where($favorite)->count()) {
            $this->apiError(-100,'您已经收藏，不能再收藏了!');
        } else {
            $favorite['create_time'] = time();
            if($this->checkUserDoFavoriteCache($favorite['uid'])){
                if (D('Favorite')->where($favorite)->add($favorite)) {
                    $this->clearCache($favorite,'favorite');
                    $uid = $this->getUid();
                    if(increaseScore($uid, 1)){
                        $extraData['scoreAdd'] = "1";
                        $extraData['scoreTotal'] = getScoreCount($uid);
                        $extra['score'] = $extraData;
                        insertScoreRecord($uid, 1, '用户收藏头条');
                    }
                    $this->apiSuccess('感谢您的支持', null, $extra);
                } else {
                    $this->apiError(-101,'写入数据库失败!');
                }
            } else {
                $this->apiSuccess('感谢您的支持');
            }
        }
    }

    /**
     * 设计头条点踩
     * @param $id
     */
    public function doOppose($id)
    {
        $this->requireLogin();
        $oppose['appname'] = 'Article';
        $oppose['table'] = 'article_content';
        $oppose['row'] = $id;
        $oppose['uid'] = is_login();
        if (M('Oppose')->where($oppose)->count()) {
            $this->apiError(-100,'您已经踩过，不能再踩了!');
        } else {
            $oppose['create_time'] = time();
            if (M('Oppose')->where($oppose)->add($oppose)) {
                unset($oppose['create_time']);
                M('Support')->where($oppose)->delete();//点踩时取消点赞
                $this->clearCache($oppose);
                $this->clearCache($oppose, 'oppose');
                $this->apiSuccess('感谢您的支持');
            } else {
                $this->apiError(-101,'写入数据库失败!');
            }
        }
    }

    /**
     * 取消设计头条点踩
     * @param $id
     */
    public function undoOppose($id)
    {
        $this->requireLogin();
        $oppose['appname'] = 'Article';
        $oppose['table'] = 'article_content';
        $oppose['row'] = $id;
        $oppose['uid'] = is_login();
        if (M('Oppose')->where($oppose)->count()) {
            if (M('Oppose')->where($oppose)->delete()) {
                $this->clearCache($oppose, 'oppose');
                $this->apiSuccess('取消点踩成功！');
            } else {
                $this->apiError(-101,'写入数据库失败!');
            }
        } else {
            $this->apiError(-102,'您还没有赞过，不能取消!');
        }
    }

    /**
     * 调整设计头条点赞和踩得数量
     * @param $id
     */
    private function adjustArticleSupportAndOpposeCount($id){

    }

    /**
     * 检查用户收藏行为是否还能加积分
     * @param int $uid
     * @return bool
     */
    public function checkUserDoFavoriteCache($uid=0){
        $data = S($uid.'_doFavorite');  //  查询用户收藏缓存
        if($data){
            $cacheData['date'] = date('Y-m-d');
            if(strtotime($cacheData['date'])>strtotime($data['date'])){  // 判断缓存是否是今天的，清空今天以前的缓存
                S($uid.'_doFavorite', array('date'=>$cacheData['date'], 'count'=>1));
                return true;
            } else {
                if($data['count']>10){   // 如果今天收藏次数超过10次，禁止再加积分
                    $count = $data['count'] + 1;
                    S($uid.'_doFavorite', array('date'=>$cacheData['date'], 'count'=>$count));
                    return false;
                } else {
                    return true;
                }
            }
        }
        return true;
    }

    /**
     * 设计头条删除收藏
     * @param $id
     */
    public function deleteFavorite($id)
    {
        $this->requireLogin();
        $favorite['appname'] = 'Article';
        $favorite['table'] = 'article_content';
        $favorite['row'] = $id;
        $favorite['uid'] = is_login();
        if (D('Favorite')->where($favorite)->count()) {
            if (D('Favorite')->where($favorite)->delete()) {
                $this->clearCache($favorite,'favorite');
                $this->apiSuccess('删除收藏成功！');
            } else {
                $this->apiError(-101,'写入数据库失败!');
            }
        } else {
            $this->apiError(-102,'您还没有收藏过，不能删除!');
        }
    }

    /**
     * 头条评论
     * @param int $id
     * @param null $content
     */
    public function doCommentOnTopContent($id=0, $content=null){
        if($id==0) {
            $this->apiError(-1, '传入头条ID为空');
        }
        if($content==null){
            $this->apiError(-1, '评论内容不能为空');
        }
        $this->requireLogin();
        $data['app'] = 'document';
        $data['mod'] = 'top_content';
        $data['row_id'] = $id;
        $data['uid'] = is_login();
        $data['content'] = $content;
        $data['create_time'] = time();
        M('LocalComment')->add($data);
        $this->apiSuccess('评论成功');
    }

    /**
     * 头条评论点赞
     * @param $id
     */
    public function doTopContentCommentSupport($id=0){
        if($id==0){
            $this->apiError(-1, '评论ID不能为空');
        }
        $this->requireLogin();
        $data['appname'] = 'Document';
        $data['table'] = 'local_comment';
        $data['row'] = $id;
        $data['uid'] = is_login();
        $data['create_time'] = time();
        M('Support')->add($data);
        M('LocalComment')->where('id='.$id)->setInc('support_count');
        $this->apiSuccess('点赞成功');
    }

    /**
     * 获取头条评论
     * @param int $id
     * @param int $page
     * @param int $count
     */
    public function getTopContentComments($id=0, $page=1, $count=10){
        if($id==0) {
            $this->apiError(-1, '传入头条ID为空');
        }
        $where_array = array(
            'status' => 1,
            'row_id' => $id,
            'app' => 'document',
            'mod' => 'top_content'
        );
        $totalCount = M('LocalComment')->where($where_array)->count();
        $comment_list = M('LocalComment')->field('id, uid, content, create_time, support_count')
            ->page($page, $count)->where($where_array)->select();
        foreach($comment_list as &$comment){
            $comment['user_info'] = $this->getAuthorStructure((int)$comment['uid']);
            $data['appname'] = 'Document';
            $data['table'] = 'local_comment';
            $data['row'] = $comment['id'];
            $data['uid'] = is_login();
            if(M('Support')->where($data)->count()){
                $comment['isSupported'] = 1;
            } else {
                $comment['isSupported'] = 0;
            }
        }
        $this->apiSuccess("获取评论列表成功", null, array('data'=>$comment_list, 'totalCount'=>$totalCount));
    }

    protected function getAuthorStructure($uid) {
        //查询数据库中的基本信息
        $map = array('id'=>$uid);
        $user = D('User/UcenterMember')->where($map)->find();
        //查询头像
        $addon = new AvatarAddon;
        $avatar = $addon->getAvatarUrl($uid);
        //返回结果
        return array(
            'uid'=>$user['id'],
            'avatar_url'=>$avatar,
            'username'=>$user['username']);
    }

    /**
     * @param $condition
     * @param string $type
     */
    private function clearCache($condition, $type='support')
    {
        unset($condition['uid']);
        unset($condition['create_time']);
        if($type == 'support')
            $cache_key = "support_count_" . implode('_', $condition);
        else if($type == 'favorite')
            $cache_key = "favorite_count_" . implode('_', $condition);
        else if($type == 'oppose')
            $cache_key = "oppose_count_" . implode('_', $condition);
        S($cache_key, null);
    }

}