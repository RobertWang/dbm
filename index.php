<?php
/**
 * 数据库管理员入口
 *
 * @author Robert <cnwangyl@qq.com>
 */

// 基本信息配置

// 获取数据库配置
// name:mode:user_level 主、从 ； 管理员、操作员
// mysql 为数据库对象名称 name
// single 为数据库部署模式 mode
// root 为用户权限级别 user_level
// root 对应完整权限 owner
// manager 对应读写权限 alter,create,drop,insert,replace,delete,select,update...
// operator 对应读权限 select,show

$dbs = [
	'mysql1' => [
		'single' => [
			'root' => [
				'scheme' => 'mysql', // 数据库类型
				'host' => '127.0.0.1', // 数据库主机地址
				'port' => '3306', // 数据库实例端口地址
				'name' => 'test', // 数据库名称
				'user' => 'root', // 访问用户
				'pass' => '123456', // 用户密码
				'xset' => 'utf-8', // 数据库字符集
			],
		],
		'master' => [
			'manager' => [
				'scheme' => 'mysql',
				'host' => '127.0.0.1',
				'port' => '3306',
				'user' => 'root',
				'pass' => '123456',
				'xset' => 'utf-8',
			],
		],
		'slave' => [
			'operator' => [
				'scheme' => 'mysql',
				'host' => '127.0.0.1',
				'port' => '3306',
				'user' => 'user',
				'pass' => '111111',
				'xset' => 'utf-8',
			],
		]
	],
];


// 读取可用的功能菜单列表
// 1. 数据库结构查看与导出
// 2. 数据库访问与查询


// 启动入口


