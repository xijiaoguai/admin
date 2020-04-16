CREATE TABLE `admin_role_relation`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `uid` int UNSIGNED NOT NULL COMMENT '用户id',
  `proj_id` int unsigned NOT NULL COMMENT '项目id',
  `role_id` int UNSIGNED NOT NULL COMMENT '角色id',
  `status` tinyint unsigned NOT NULL DEFAULT '0' COMMENT '状态（0正常1删除）',
  PRIMARY KEY (`id`),
  INDEX `uid`(`uid`),
  INDEX `role_id`(`role_id`),
  INDEX `proj_id`(`proj_id`),
  INDEX `status`(`status`)
) COMMENT = '角色关系表';