CREATE TABLE `admin_proj`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL COMMENT '项目名',
  `desc` varchar(255) NOT NULL DEFAULT '' COMMENT '项目简介',
  `team_id` int UNSIGNED NOT NULL COMMENT '所属团队',
  `status` tinyint(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '状态（0正常1删除）',
  `create` timestamp(0) NOT NULL DEFAULT CURRENT_TIMESTAMP(0),
  `update` timestamp(0) NOT NULL DEFAULT CURRENT_TIMESTAMP(0) ON UPDATE CURRENT_TIMESTAMP(0),
  PRIMARY KEY (`id`),
  INDEX `team_id`(`team_id`),
  INDEX `status`(`status`)
) COMMENT = '用户项目表';