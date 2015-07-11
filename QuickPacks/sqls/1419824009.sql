delete from `opensns_menu` where  `url` like 'Forum/%';
INSERT INTO `opensns_menu` (`title`, `pid`, `sort`, `url`, `hide`, `tip`, `group`, `is_dev`) VALUES
( '论坛', 0, 22, 'Forum/index', 0, '', '', 0);
set @tmp_id=0;
select @tmp_id:= id from `opensns_menu` where title = '论坛';

INSERT INTO `opensns_menu` ( `title`, `pid`, `sort`, `url`, `hide`, `tip`, `group`, `is_dev`) VALUES
( '板块管理', @tmp_id, 1, 'Forum/forum', 0, '', '板块', 0),
( '帖子管理', @tmp_id, 3, 'Forum/post', 0, '', '帖子', 0),
( '编辑／创建板块', @tmp_id, 0, 'Forum/editForum', 1, '', '', 0),
( '编辑帖子', @tmp_id, 0, 'Forum/editPost', 1, '', '', 0),
( '排序', @tmp_id, 0, 'Forum/sortForum', 0, '', '板块', 0),
( '新增/编辑回复', @tmp_id, 0, 'Forum/editReply',1, '', '', 0),
( '板块回收站', @tmp_id, 2, 'Forum/forumTrash', 0, '', '板块', 0),
( '帖子回收站', @tmp_id, 4, 'Forum/postTrash', 0, '', '帖子', 0),
( '回复回收站', @tmp_id, 6, 'Forum/replyTrash', 0, '', '回复', 0),
( '回复管理', @tmp_id, 6, 'Forum/reply', 0, '', '回复', 0),
( '论坛设置', @tmp_id, 10, 'Forum/config', 0, '', '设置', 0),
( '新增/编辑分类', @tmp_id, 0, 'Forum/addType', 1, '', '', 0),
( '设置分类状态', @tmp_id, 0, 'Forum/setTypeStatus', 1, '', '', 0),
( '分类管理', @tmp_id, 0, 'Forum/type', 0, '分类管理', '分类管理', 0);

ALTER TABLE  `opensns_forum` ADD  `last_reply_time` INT NOT NULL;


INSERT INTO `opensns_config` ( `name`, `type`, `title`, `group`, `extra`, `remark`, `create_time`, `update_time`, `status`, `value`, `sort`) VALUES
( 'DEFUALT_HOME_URL', 1, '默认首页Url', 1, '', '支持形如weibo/index/index的ThinkPhp路由写法，支持普通的url写法，不填则显示默认聚合首页', 1417509438, 1417509501, 1, 'Weibo/index/index', 0);
ALTER TABLE  `opensns_auth_group` CHANGE  `rules`  `rules` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT  '' COMMENT  '用户组拥有的规则id，多个规则 , 隔开';
