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
	'test' => [
		'scheme' => 'mysql', // 数据库类型
		'host' => 'mysql', // 数据库主机地址
		'port' => '3306', // 数据库实例端口地址
		'name' => 'test', // 数据库名称
		'user' => 'root', // 访问用户
		'pass' => '123456', // 用户密码
		'xset' => 'utf-8', // 数据库字符集
	],
	'local' => [
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
	],
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

/* 
// reset autoindex to 1
ALTER TABLE `m_admin_redis` AUTO_INCREMENT = 1;

SHOW TABLE STATUS LIKE 'm_admin_redis';

SHOW CREATE TABLE `m_admin_redis`;
*/

// 启动入口
define('ROOT', __DIR__);
require_once(ROOT.'/lib/driver.class.php');
require_once(ROOT.'/lib/driver/mysql.class.php');
$mysql_dao = new MysqlDriver();
$mysql_dao->SetConnect($dbs['test']);
$sql = "show tables";
$list = $mysql_dao->Query($sql);

foreach ( $list as $i => $table ) {
	$table_name = $table['Tables_in_test'];
	echo '<h1>', $table_name, '</h1>', PHP_EOL;

	$sql = "show full columns from `{$table_name}`";
	$struct = $mysql_dao->Query($sql);
	echo show_table($struct);

	$sql = "show index from `{$table_name}`";
	$index = $mysql_dao->Query($sql);
	echo show_table($index);

	$sql = "show table status like '{$table_name}'";
	$status = $mysql_dao->Query($sql);
	echo show_table($status, false);


	if ( $i >= 5 ) {
		exit();
	}
}

function show_table ( $res, $direct=true ) {
	if ( empty($res) ) {
		return '';
	}
	$html = [];
	if ( $direct == true ) {
		array_push($html, '<table boreder=1>');
		array_push($html, '<tr>');
		foreach ( $res[0] as $field => $value ) {
			array_push($html, '<th>'.$field.'</th>');
		}
		array_push($html, '</tr>');
		foreach ( $res as $i => $rec ) {
			array_push($html, '<tr>');
			foreach ( $rec as $field => $value ) {
				array_push($html, '<td>'.$value.'</td>');
			}
			array_push($html, '</tr>');
		}
		array_push($html, '<table>');
	} else {
		array_push($html, '<table boreder=1>');
		foreach ( $res as $i => $rec ) {
			array_push($html, '<tr><table>');
			foreach ( $rec as $field => $value ) {
				array_push($html, '<tr>');
				array_push($html, '<td>'.$field.'</td>');
				array_push($html, '<td>'.$value.'</td>');
				array_push($html, '</tr>');
			}
			array_push($html, '</table></tr>');
		}
		array_push($html, '<table>');
	}
	$html = implode(PHP_EOL, $html);

	return $html;
}


