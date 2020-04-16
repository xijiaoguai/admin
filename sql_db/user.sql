CREATE TABLE `admin_user` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `acc` varchar(50) NOT NULL COMMENT '账号',
  `pwd` varchar(50) NOT NULL COMMENT '密码',
  `entry` char(6) NOT NULL COMMENT '密码加密字符串',
  `team_id` int NOT NULL DEFAULT '0' COMMENT '团队id',
  `create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `acc` (`acc`)
) COMMENT '用户表';