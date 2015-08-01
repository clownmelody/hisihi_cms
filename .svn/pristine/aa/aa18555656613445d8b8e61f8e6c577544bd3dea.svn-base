ALTER TABLE  `opensns_check_info` ADD  `id` INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST;
create table `opensns_tmp` select max(`id`) as `id` from `opensns_check_info` group by `uid`;
 
 
create table `opensns_tmp2` select `opensns_check_info`.* from `opensns_check_info`,`opensns_tmp` where `opensns_check_info`.`id` = `opensns_tmp`.`id`;
 
 
drop table `opensns_check_info`;
 
 
rename table `opensns_tmp2` to `opensns_check_info`;

alter table `opensns_check_info` drop column `id`;