DELETE FROM `opensns_hooks` WHERE `name` = 'userRegister';
INSERT INTO `opensns_hooks` ( `name`, `description`, `type`, `update_time`, `addons`) VALUES
( 'userRegister', '用户注册钩子，参数uid为用户ID', 2, 1419563244, '');

DELETE FROM `opensns_menu` WHERE `url` like 'Admin/Update%';
DELETE FROM `opensns_menu` WHERE `url` like 'Update/%';
INSERT INTO `opensns_menu` ( `title`, `pid`, `sort`, `url`, `hide`, `tip`, `group`, `is_dev`) VALUES
( '全部补丁', 68, 0, 'Update/quick', 0, '', '升级补丁', 0),
( '新增补丁', 68, 0, 'Update/addpack', 1, '', '升级补丁', 0);
