CREATE TABLE `admin_team` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL COMMENT '团队名称',
  `desc` varchar(1000) NOT NULL COMMENT '描述',
  `crt_id` int unsigned NOT NULL COMMENT '创建者id',
  `mark` char(4) NOT NULL COMMENT '团队标识',
  `status` tinyint unsigned NOT NULL DEFAULT '0' COMMENT '状态（0正常1删除）',
  `create` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '修改时间',
  PRIMARY KEY (`id`),
  KEY `mark` (`mark`),
  KEY `crt_id` (`crt_id`),
  KEY `status` (`status`)
) COMMENT = '团队表';