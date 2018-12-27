<?php
/**
 * 数据库驱动类
 *
 * @author Robert <cnwangyl@qq.com>
 */

class Driver {
	/**
	 * @var $alias string 连接别名
	 */
	protected $alias = 'default';
	/**
	 * @var $driver string 连接类型
	 */
	protected $driver = 'pdo_mysql';
	/**
	 * @var $config array 一个数据库连接的配置信息
	 */
	protected $config = [];
	/**
	 * @var $dao Driver 连接对象
	 */
	protected $dao = null;

	/**
	 * 解析 dsn 字符串
	 */
	public function ParseDSN($dsn) {
		$config = [];
		return true;
	}

}