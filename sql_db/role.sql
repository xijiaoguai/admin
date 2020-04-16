CREATE TABLE `admin_role`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL COMMENT '角色名',
  `proj_id` int unsigned NOT NULL COMMENT '项目id',
  `create` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `update` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `status` tinyint unsigned NOT NULL DEFAULT '0' COMMENT '状态（0正常1删除）',
  PRIMARY KEY (`id`),
  INDEX `proj_id`(`proj_id`),
  INDEX `status`(`status`)
) COMMENT = '角色表';