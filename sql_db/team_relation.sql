CREATE TABLE `admin_team_relation` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `uid` int NOT NULL COMMENT '用户id',
  `team_id` int unsigned NOT NULL COMMENT '团队id',
  `status` tinyint unsigned NOT NULL DEFAULT '0' COMMENT '状态（0正常1退出）',
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`),
  KEY `team_id` (`team_id`),
  KEY `status` (`status`)
) COMMENT '团队关系表';