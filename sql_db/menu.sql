CREATE TABLE `admin_menu`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL COMMENT '菜单名称',
  `url` varchar(255) NOT NULL DEFAULT '' COMMENT '菜单地址',
  `proj_id` int UNSIGNED NOT NULL COMMENT '所属项目id',
  `pid` int UNSIGNED NOT NULL DEFAULT 0 COMMENT '所属项目id',
  `status` tinyint unsigned NOT NULL DEFAULT '0' COMMENT '状态（0正常1删除）',
  `crt_id` int UNSIGNED NOT NULL DEFAULT 0 COMMENT '创建者id',
  PRIMARY KEY (`id`),
  INDEX `proj_id`(`proj_id`),
  INDEX `pid`(`pid`),
  INDEX `status` (`status`)
) COMMENT = '菜单表';