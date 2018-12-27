<?php
/**
 * MySQL 数据库连接类
 *
 * @author Robert <cnwangyl@qq.com>
 */

class MysqlDriver extends Driver
{

	public function SetConnect($dsn, $alias='default')
	public function ConnectDSN($dsn) {}
	public function ConnectAlias($alias) {}

	public function Query() {}
	public function Execute() {}

	public function Close() {}

}