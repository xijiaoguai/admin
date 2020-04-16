CREATE TABLE `admin_menu_relation`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `menu_id` int UNSIGNED NOT NULL COMMENT '菜单id',
  `role_id` int UNSIGNED NOT NULL COMMENT '角色id',
  PRIMARY KEY (`id`),
  INDEX `menu_id`(`menu_id`),
  INDEX `role_id`(`role_id`)
) COMMENT = '菜单关系表';