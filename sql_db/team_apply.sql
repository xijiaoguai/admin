CREATE TABLE `admin_team_apply` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `uid` int NOT NULL COMMENT '用户id',
  `team_id` int unsigned NOT NULL COMMENT '团队id',
  `status` tinyint unsigned NOT NULL DEFAULT '0' COMMENT '状态（0申请中1通过2拒绝3撤销）',
  `remarks` varchar(255) NOT NULL DEFAULT '' COMMENT '备注',
  `create` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '申请时间',
  `update` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  PRIMARY KEY (`id`)
) COMMENT = '团队申请表';