<?php
/** 1、
 * 注册 register
 * @param  string $username 用户名
 * @param  string $password 用户密码
 * @param  string $email 用户邮箱
 * @param  string $mobile 用户手机号码
 * @param  string $group 用户组  5 设计尸 6 讲师 设计师可默认不填写
 * @return integer          注册成功-用户信息，注册失败-错误编号
 */
//调用实例：http://115.29.44.35/api.php?s=/user/register/username/xxx/password/xxx/email/xxx/mobile/xxx/group/6

/** x、
 * 注册 registerByMobile
 * @param  string $mobile,
 * @param  string $password 用户密码
 * @param  string $group 用户组  5 设计尸 6 讲师 设计师可默认不填写
 * @return integer          注册成功-用户信息，注册失败-错误编号
 */
//调用实例：http://115.29.44.35/api.php?s=/user/registerByMobile/mobile/xxx/password/xxx/group/6

/** 2、
 * 登录 login
 * @param  string $username 用户名
 * @param  string $password 用户密码
 * @param  integer $type 用户名类型 （1-用户名，2-邮箱，3-手机，4-UID）
 * @param  integer $client 客户端类型 （1-Android，2-iOS，3-微信，4-网站）
 * @return            登录成功-用户ID，登录失败-错误编号
 * session_id
 * uid 用户编号
 * name 昵称
 * avatar_url 头像地址
 * avatar128_url 头像地址 128*128
 * signature 个性签名
 * tox_money 积分
 * title 等级
 * ischeck 是否签到
 *
 **/
//调用实例：http://115.29.44.35/api.php?s=/user/login/username/xxx/password/xxx/type/1
//http://www.hisihi.com/api.php?s=/user/login/username/test9/password/qq123456789000

/*********注（重要）：以下接口全部需要带session_id去请求，session_id来自登录后的返回********/
/** 3、
 * 注销 logout
 * @return integer          注销成功-成功信息，注销失败-错误信息
 */
//调用实例：http://115.29.44.35/api.php?s=/user/logout

/** 4、
 * 签到 checkin
 * @return       签到成功-成功信息，签到失败-错误信息
 * checkInfo {con_num 连续签到天数，total_num 签到总天数}
 */
//调用实例：http://115.29.44.35/api.php?s=/user/checkin

/** 5、
 * 发送验证码 sendVerify （弃用）使用Mob免费短信验证码接口验证
 * @param  string $mobile 手机号
 * @return integer          发送成功-成功信息，发送失败-错误信息
 */
//调用实例：http://115.29.44.35/api.php?s=/user/sendVerify/mobile/xxx

/** 6、
 * 密码重置 resetPasswordByMobile
 * @param  string $mobile 手机号
 * @param  string $verify 验证码（通过Mob验证码sdk获取到的验证码）
 * @param  string $new_password 新密码
 * @return integer          重置成功-成功信息，重置失败-错误信息
 */
//调用实例：http://115.29.44.35/api.php?s=/user/resetPasswordByMobile/mobile/xxx/new_password/xxx

/** 7、
 * 密码修改 changePassword
 * @param  string $old_password 旧密码
 * @param  string $new_password 新密码
 * @return integer          修改成功-成功信息，修改失败-错误信息
 */
//调用实例：http://115.29.44.35/api.php?s=/user/changePassword/old_password/xxx/new_password/xxx

/** 8、
 * 上传头像 uploadAvatar  头像必须为长宽一致，需要在客户端先对图片处理
 * @param  file  image    头像表单内容 表单名必须是image
 * @return integer          上传成功-成功信息，上传失败-错误信息
 */
//调用实例：http://115.29.44.35/api.php?s=/user/uploadAvatar/

/** 9、
 * 获取资料 getProfile
 * @return integer          获取成功-成功信息，获取失败-错误信息
 * uid 用户编号
 * avatar_url 头像地址
 * avatar128_url 头像地址 128*128
 * signature 个性签名
 * email 邮箱
 * mobile 手机
 * tox_money 积分
 * name 昵称
 * title 等级
 * sex 性别 m f s
 * birthday 生日
 * ischeck 签到信息 同签到接口
 * username 用户名
 * group 角色   5 设计师 6 讲师
 * extinfo 扩展认证信息
 * {
 * "id": "39", 信息编号
 * "field_name": "institution",  字段名
 * "field_title": "任职机构",  描述
 * "field_content": "嘿设汇" 字段值
 * }
 */
//调用实例：http://115.29.44.35/api.php?s=/user/getProfile
//返回示例：

/** 10、
 * 修改资料 setProfile
 * @param  string signature 签名
 * @param  string email 邮箱
 * @param  string name 名字
 * @param  string sex 性别 s（保密） m（男） f（女）
 * 以下参数分角色填写，如果是设计师填前三个，如果是讲师填后三个
 * @param  string birthday 生日 格式：0000-00-00
 * @param  string college 学校
 * @param  string major 专业
 * @param  string grade 年级
 * @param  string institution 机构
 * @param  string student 学生个数
 * @param  string year 年限
 * @return integer          获取成功-成功信息，获取失败-错误信息
 */
//调用实例：http://115.29.44.35/api.php?s=/user/setProfile
//返回示例：

/** 11、
 * 加关注 followUser
 * @param  int uid 用户id
 * @return integer          获取成功-成功信息，获取失败-错误信息
 */
//调用实例：http://115.29.44.35/api.php?s=/user/followuser/uid/xxx
//返回示例：

/** x、
 * 取消关注 unfollowUser
 * @param  int uid 用户id
 * @return integer          获取成功-成功信息，获取失败-错误信息
 */
//调用实例：http://115.29.44.35/api.php?s=/user/unfollowuser/uid/xxx
//返回示例：

/** x、
 * 关注列表 following
 * @param  uid 不填就是自己
 * @param  page 页数
 * @param  count 每页条数
 * @return integer          获取成功-成功信息，获取失败-错误信息
 * totalCount  总数
 * followingList 关注列表
 * {
 * "follow_who": "60", 关注人id
 * "user": {
 * "title": "Lv3 转正", 级别
 * "avatar128": "Addons/Avatar/default_128_128.jpg", 头像地址 128*128
 * "uid": "60", 关注人id
 * "nickname": "qwer1234", 昵称
 * "fans": "2", 粉丝数
 * "following": "1" 关注数
 * }
 * }
 */
//调用实例：http://115.29.44.35/api.php?s=/user/following
//返回示例：

/** x、
 * 粉丝列表 fans
 * @return integer          获取成功-成功信息，获取失败-错误信息
 * * totalCount  总数
 * followingList 关注列表
 * {
 * "follow_who": "60", 关注人id
 * "user": {
 * "title": "Lv3 转正", 级别
 * "avatar128": "Addons/Avatar/default_128_128.jpg", 头像地址 128*128
 * "uid": "60", 关注人id
 * "nickname": "qwer1234", 昵称
 * "fans": "2", 粉丝数
 * "following": "1" 关注数
 * }
 * }
 */
//调用实例：http://115.29.44.35/api.php?s=/user/fans
//返回示例：

/** x、
 * 用户列表 find
 * @param  count 每页条数
 * @param  page  页码 不填默认为1
 * @param  keywords   用户名关键字，模糊查询，可不填
 * @param  group 用户组 5 设计师  6 讲师  不填默认为设计师
 * @param  tab  'fans' 'question' 'answer'  分别对应 粉丝最多 提问最多 回答最多
 * @return integer          获取成功-成功信息，获取失败-错误信息
 */
//调用实例：http://115.29.44.35/api.php?s=/user/find/page/xx/group/6/keywords/xx
//返回示例：

/** 11、
 * 收藏列表 listFavorite
 * @return integer          获取成功-成功信息，获取失败-错误信息
 */
//调用实例：http://115.29.44.35/api.php?s=/user/listFavorite
//返回示例：

/** 12、
 * 删除收藏 deleteFavorite
 * @param ￥id=收藏编号
 * @return integer          删除成功-成功信息，删除失败-错误信息
 */
//调用实例：http://115.29.44.35/api.php?s=/user/deleteFavorite/id/xx
//返回示例：

/** x、
 * 消息列表 message
 * @param  page  页码
 * @param  tab   消息类型 'system' 'app' 'user' 'all'
 * @return integer          删除成功-成功信息，删除失败-错误信息
 */
//调用实例：http://115.29.44.35/api.php?s=/user/message/page/xx/tab/xx

/** x、
 * 标记为已读消息 readMessage
 * @param  id  消息编号
 * @return integer          删除成功-成功信息，删除失败-错误信息
 */
//调用实例：http://115.29.44.35/api.php?s=/user/readmessage/id/xx

/** x、
 * 标记所有消息已读 setAllMessageReaded
 * @return integer          删除成功-成功信息，删除失败-错误信息
 */
//调用实例：http://115.29.44.35/api.php?s=/user/setAllMessageReaded

/** 13、
 * 商城列表 listGoods
 * $page
 * $r
 * @return
 */
//调用实例：http://115.29.44.35/api.php?s=/shop/listgoods
//返回示例：

/** 13、
 * 商城列表 myGoods
 * $page
 * $status
 * @return
 */
//调用实例：http://115.29.44.35/api.php?s=/shop/mygoods
//返回示例：

/** 14、
 * 兑换商品 buyGoods
 * @param $id = 0, $name = '', $address = '', $zipcode = '', $phone = '', $address_id = ''
 * @return
 */
//调用实例：http://115.29.44.35/api.php?s=/shop/goodsBuy/id/xxx/name/xx/address/xx/zipcode/xx/phone/xx/address_id/xx
//返回示例：

/** 15、
 * 社区列表 Forum
 * @param $type_id = 0(对应筛选的分类的id,forumType接口获取的大类id，不是forums内的id), $page = 1（页码）, $is_reply = -1（是否有回答 -1:不筛选 0:无回答 1:有回答）, $order = 'reply' （排序规则 ctime:最新发帖 reply:最新回复）
 * @return img 图片  sound 声音
 */
//调用实例：http://115.29.44.35/api.php?s=/Forum/forum
//返回示例：

/** 15、
 * 社区指定用户列表 userForumList
 * param $uid = null（用户id，null为当前登录用户）, $page = 1（页码）, $count = 10（每页条数）, $tab = null（筛选规则 forum:发帖 forum_in:回答）
 * @return img 图片  sound 声音
 */
//调用实例：http://115.29.44.35/api.php?s=/Forum/userForumList
//返回示例：

/** 16、
 * 标签/分类 forumType
 * @return
 */
//调用实例：http://115.29.44.35/api.php?s=/Forum/forumType
//返回示例：

/** x、
 * 提问详情 detail
 * @param $id, $page = 1
 * @return
 */
//调用实例：http://115.29.44.35/api.php?s=/Forum/detail/id/xx/page/xx
//返回示例：

/** x、
 * 上传图片 uploadpicture
 * @param post表单方式上传，可以一次上传最大9张
 * @return  pictures 以','分割的id串
 */
//调用实例：http://115.29.44.35/api.php?s=/Forum/uploadpicture
//返回示例：

/** x、
 * 上传声音 uploadsound
 * @param post表单方式上传，每条发帖对应一个sound
 * @return  sound id
 */
//调用实例：http://115.29.44.35/api.php?s=/Forum/uploadsound
//返回示例：

/** x、
 * 提问 dopost
 * @param $post_id = 0, $forum_id = 类别/标签，forumType获取到的forums小类id, $title = 标题, $content = 内容, $pos = 位置信息（例如：湖北-武汉）, $pictures = uploadpicture 的返回结果 pictures 以‘，’分割的id串，$sound = uploadsound 返回的sound id结果
 * @return
 */
//调用实例：http://115.29.44.35/api.php?s=/Forum/dopost/post_id/0/forum_id/xx/title/xx/content/xx
//返回示例：
//http://www.hisihi.com/api.php?session_id=injgb6mk3249lmeeeisu8ofum3&s=/Forum/dopost/$post_id/0/forum_id/1/title/test/content/testimg/pictures/134,136/sound/2

/** 17、
 * 回答 doReply
 * @param $post_id = 提问对应的id, $content = 内容  $pos = 位置信息（例如：湖北-武汉）, $pictures = uploadpicture 的返回结果 pictures 以‘，’分割的id串，$sound = uploadsound 返回的sound id结果
 * @return
 */
//调用实例：http://115.29.44.35/api.php?s=/Forum/doReply/$post_id/xxx/content/xx
//返回示例：

/** x、
 * 追问 doSendLZLReply （楼中楼）
 * @param $post_id = 提问对应的id, $to_f_reply_id = 对应回答的id, $to_reply_id = 对应lzl回答的id, $to_uid = 追问用户id, $content = 内容, $pos = 位置信息（例如：湖北-武汉）, $pictures = uploadpicture 的返回结果 pictures 以‘，’分割的id串，$sound = uploadsound 返回的sound id结果,,$p=1
 * @return
 */
//调用实例：http://115.29.44.35/api.php?s=/Forum/doSendLZLReply/$post_id/xxx/$to_f_reply_id/xx/......
//返回示例：

/** x、
* 点赞 doSupport
* @param $type = post  reply, $id = 提问id或者回答id
    * @return
 */
//调用实例：http://115.29.44.35/api.php?s=/Forum/doSupport/......
//返回示例：

/** x、
 * 取消点赞 unDoSupport
 * @param $type = post  reply, $id = 提问id或者回答id
 * @return
 */
//调用实例：http://115.29.44.35/api.php?s=/Forum/unDoSupport/......
//返回示例：

/** x、
 * 云作业类别 category
 * @param $type = post  reply, $id = 提问id或者回答id
 * @return
 */
//调用实例：http://115.29.44.35/api.php?s=/Hiworks/category
//返回示例：

/** x、
 * 云作业扫描 scan
 * @param $guid = 扫描到的二维码内容
 * @return
 */
//调用实例：http://115.29.44.35/api.php?s=/Qrscan/scan
//返回示例：

/** x、
 * 意见反馈 suggest
 * @param $content = 要反馈的内容
 * @return
 */
//调用实例：http://115.29.44.35/api.php?s=/public/suggest/content/xxx
//返回示例：

/** x、
 * 数字统计 statInfo
 * @return
 */
//调用实例：http://115.29.44.35/api.php?s=/public/statinfo
//返回示例：

/** x、
 * 课程类别 courseType
 * @param
 * @return
 */
//调用实例：http://115.29.44.35/api.php?s=/Course/courseType
//返回示例：


/** 18、
 * 课程列表 listCourses
 * @param $type_id = 筛选类型id $page = 页码, $count = 每页个数, $order = 排序规则  view = 观看最多|reply = 评论最多, $keywords = 搜索关键词
 * @return
 */
//调用实例：http://115.29.44.35/api.php?s=/Course/listCourses
//返回示例：

/** 19、
 * 课程详情 courseDetail
 * @param $id = 课程id  //注：调用该接口后，课程观看次数+1
 * @return
 */
//调用实例：http://115.29.44.35/api.php?s=/Course/courseDetail/id/xxx
//返回示例：

/** x、
 * 课程详情分享URL courseShareURL
 * @param $id = 课程id  //注：调用该接口后，课程观看次数+1
 * @return
 */
//调用实例：http://115.29.44.35/api.php?s=/Course/courseShareURL/id/xxx
//返回示例：

/** x、
 * 评论课程 doComment
 * @param $id = 课程id  $content = 评论内容
 * @return
 * */
//调用实例：http://115.29.44.35/api.php?s=/Course/doComment/id/xxx/content/xx
//返回示例：

/** x、
 * 课程评论列表 commentList
 * @param $id = 课程id  $page = 页码, $count = 每页个数
 * @return
 * */
//调用实例：http://115.29.44.35/api.php?s=/Course/commentList/id/xxx
//返回示例：

/** x、
 * 收藏 doFavorite
 * @param $id = 课程id
 * @return
 */
//调用实例：http://115.29.44.35/api.php?s=/Course/doFavorite/id/xx
//返回示例：

/** x、
 * 删除收藏 deleteFavorite
 * @param $id = 课程id
 * @return
 */
//调用实例：http://115.29.44.35/api.php?s=/Course/deleteFavorite/id/xx
//返回示例：

/** x、
 * 点赞 doSupport
 * @param $id = 课程id
 * @return
 */
//调用实例：http://115.29.44.35/api.php?s=/Course/doSupport/id/xx
//返回示例：

/** x、
 * 取消点赞 unDoSupport
 * @param $id = 课程id
 * @return
 */
//调用实例：http://115.29.44.35/api.php?s=/Course/unDoSupport/id/xx
//返回示例：

/** x、
 * 首页顶部列表 topList
 * @return
 */
//调用实例：http://115.29.44.35/api.php?s=/public/topList
//返回示例：

/** x、暂时不用了 改为url实现
 * 首页顶部详情页 topContent
 * @param $id = 文章id
 * @return
 */
//调用实例：http://115.29.44.35/api.php?s=/public/topContent
//返回示例：

/** 20、
 * 首页推荐课程列表 RecommendCourse
 * @param $page = 页码, $count = 每页个数
 * @return
 */
//调用实例：http://115.29.44.35/api.php?s=/Course/recommendcourses
//返回示例：

/** 21、
 * 首页猜你喜欢课程列表 guessULikeCourses
 * @return
 */
//调用实例：http://115.29.44.35/api.php?s=/Course/guessULikeCourses
//返回示例：


/** 22、
 * 发现 listJob 待定
 * @return
 */
//调用实例：http://115.29.44.35/api.php?s=/find/listjob
//返回示例：

/** 23、
 * 发现 openWork 待定
 * @return
 */
//调用实例：http://115.29.44.35/api.php?s=/find/openWork
//返回示例：