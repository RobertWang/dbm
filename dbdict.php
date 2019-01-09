<?php
/**
 * 数据库管理员入口
 *
 * @author Robert <cnwangyl@qq.com>
 */
define('COOKIE_PREFIX', 'dbm-');
define('IS_CLI', false);

define('DS', DIRECTORY_SEPARATOR);
define('ROOT', __DIR__.DS);


ob_start();

// 加载默认配置文件
if ( !file_exists(ROOT.'config.php') ) {
	show_error_page('没有配置文件');
}

// 显示错误信息页面
function html_error($msg) {
	var_dump(L());
	die($msg);
}

function L($msg=null, $stick=false) {
	static $logs = array();
	if ( is_null($msg) ) {
		return $logs;
	}

	if ( is_string($msg) ) {
		$_msg = array('msg'=>$msg);
		if ( $stick ) {
			$_msg['trace'] = $stick;
		}
		array_push($logs, $_msg);
		return true;
	}
	return false;
}

// 读取系统配置
L('[INFO] 加载配置文件');
$config = include(ROOT.'config.php');

L('[INFO] 加载配置文件中的数据库访问参数');
$config_db = false;
if ( isset($config['db']) && is_array($config['db']) ) {
	// html_error('尚未配置数据库访问参数');
	$config_db = &$config['db'];
}
L('[INFO] '.($config_db?'数据库访问参数加载成功':'配置文件中未配置数据库访问参数'));

// 是否允许通过页面填写数据库访问地址
$config['allow_custom_dbconfig'] = isset($config['allow_custom_dbconfig']) ? !! $config['allow_custom_dbconfig'] : true;
L('[INFO] '.($config['allow_custom_dbconfig'] ? '允许' : '不允许').'通过页面配置数据库访问参数');

L('[INFO] 加载 COOKIE 中的数据库访问参数');
$config_cookie_name = COOKIE_PREFIX . 'dbconfig';
$cookie_db = isset($_COOKIE[$config_cookie_name]) ? json_decode($_COOKIE[$config_cookie_name], true) : false;
L('[INFO] '.($cookie_db?'临时数据库访问参数加载成功':'COOKIE 中未配置数据库访问参数'));

if ( !$config['allow_custom_dbconfig'] && !$config_db && !$cookie_db ) {
	html_error('尚未配置数据库访问参数');
}

$dbconfig = false;
$msg = '';
$link = null;

/**
 * 根据请求参数进行路由选择
 *
 */
function dispatch() {
	// 请求类型
	$method = strtolower(trim($_SERVER['REQUEST_METHOD']));
	$allowed_methods = array('get', 'post');
	if ( !in_array($method, $allowed_methods) ) {
		html_error('请求异常');
	}
	$is_ajax = util_isajax();

	$fun_prefix = '';
	if ( $is_ajax ) {
		$fun_prefix = 'ajax';
	} else {
		$fun_prefix = $method;
	}

	$target = before_dispatch($method);

	$fun_name = $fun_prefix.'_'.$target;
	L('[INFO] 当前请求方法 '.$fun_name.' ( 通过 '.$method.($is_ajax?' ajax':'').' )');
	if ( function_exists($fun_name) ) {
		call_user_func_array($fun_name, array());
	} else {
		html_error('错误的请求操作');
	}
}

function before_dispatch($method='get') {
	global $config, $dbconfig, $cookie_db, $config_db, $link;

	// 目标页面
	$target = isset($_GET['t']) ? strtolower(trim($_GET['t'])) : '';

	if ( $cookie_db ) {
		$dbconfig = $cookie_db;
	} else {
		if ( $config_db ) {
			$dbconfig = $dbconfig;
		} else {
			if ( $config['allow_custom_dbconfig'] ) {
				// show_page('custom_dbconfig_form');
				if ( $method === 'post' ) {
					die(post_save_custom_dbaccess());
				} else {
					die(html_custom_dbaccess());
				}
			} else {
				html_error('尚未配置数据库访问参数');
			}
		}
	}

	$ret = check_db_link();
	if ( !$ret ) {
		html_error('数据库无法访问');
	}
	if ( !$link ) {
		html_error('数据库访问请求失败');
	}


	if ( $target == '' ) {
		$target = 'index';
	}
	return $target;
}


dispatch();
die();
/*
	// 登录
	// 1. 通过 http 访问 将 post 参数中的数据验证通过后，保存至 session 中
	// 2. 通过 shell 访问，通过 一个配置文件读取数据库访问配置

	// <> 通过 执行后续
	// <> 未通过 返回

	$guid = isset($_COOKIE[COOKIE_PREFIX.'GUID']) ? trim($_COOKIE[COOKIE_PREFIX.'GUID']) : false;
	session_start();

	$sessid = session_id();
	$userinfo = $_SESSION;
	var_dump($userinfo);
*/




function get_index() {
	// 方法1 查看数据表字典信息
	$result = get_tables_info();
	echo html_tables($result);

}

// var_dump('ret', $ret);
// var_dump('msg', $msg);
// var_dump('link', $link);
// echo str_repeat('- - - ', 20), PHP_EOL;


// echo json_encode($result);
exit;
function html_tables($tables) {
	$html = [];

	array_push($html, '<!DOCTYPE html><html lang="zh-CN">');
	array_push($html, '<head><meta charset="utf-8">');
	array_push($html, '<meta http-equiv="X-UA-Compatible" content="IE=edge">');
	array_push($html, '<meta name="viewport" content="width=device-width, initial-scale=1">');
	array_push($html, '<title>数据字典</title>');
	array_push($html, '<link href="https://cdn.jsdelivr.net/npm/bootstrap@3.3.7/dist/css/bootstrap.min.css" rel="stylesheet">');
	array_push($html, '<link href="data:text/css;charset=utf-8," data-href="../dist/css/bootstrap-theme.min.css" array_push($html, rel="stylesheet" id="bs-theme-stylesheet">');
	array_push($html, '<link href="https://v3.bootcss.com/assets/css/docs.min.css" rel="stylesheet">');
	array_push($html, '<link href="https://v3.bootcss.com/assets/css/patch.css" rel="stylesheet">');
	array_push($html, '<!--[if lt IE 9]><script src="https://v3.bootcss.com/assets/js/array_push($html, ie8-responsive-file-warning.js"></script><![endif]-->');
	array_push($html, '<script src="https://v3.bootcss.com/assets/js/ie-emulation-modes-warning.js"></script>');
	array_push($html, '<!--[if lt IE 9]>');
	array_push($html, '<script src="https://cdn.jsdelivr.net/npm/html5shiv@3.7.3/dist/html5shiv.min.js"></script>');
	array_push($html, '<script src="https://cdn.jsdelivr.net/npm/respond.js@1.4.2/dest/respond.min.js"></script>');
	array_push($html, '<![endif]-->');
	array_push($html, '</head>');

	array_push($html, '<body>');

	array_push($html, '<div class="bs-docs-header" id="content" tabindex="-1"><div class="container"><h1><i class="glyphicon glyphicon-book"></i> 数据库字典</h1><p></p></div></div>');
	
	// 基本框架
	array_push($html, '<div class="container bs-docs-container"><div class="row">');

	// 左侧
	array_push($html, '<div class="col-md-9" role="main">');
	array_push($html, '<div class="bs-docs-section">');
	array_push($html, '<h1 id="tables" class="page-header"><a class="anchorjs-link " href="#tables" aria-label="Anchor link for: tables" data-anchorjs-icon="" style="font-family: anchorjs-icons; font-style: normal; font-variant: normal; font-weight: normal; line-height: inherit; position: absolute; margin-left: -1em; padding-right: 0.5em;"></a>数据表</h1>');

	foreach ( $tables as $i => $table ) {
		$table_id = 'id_'.trim($table['name']);
		array_push($html, '<h2 id="'.$table_id.'"><a class="anchorjs-link " href="#'.$table_id.'" aria-label="Anchor link for: '.$table_id.'" data-anchorjs-icon="" style="font-family: anchorjs-icons; font-style: normal; font-variant: normal; font-weight: normal; line-height: inherit; position: absolute; margin-left: -1em; padding-right: 0.5em;"></a><i class="glyphicon glyphicon-list-alt"></i> '.($i+1).'. '.$table['name'].' ('.$table['engine'].')</h2>');
		$comment = util_safe_encoding($table['comment']);
		array_push($html, '<p>'.$comment.'</p>');

		// 字段列表
		array_push($html, '<div>');
		array_push($html, '<table class="table table-hover table-bordered">');
		array_push($html, '<thead><tr><th>列名</th><th>类型</th><th title="是否设置了索引">索引</th><th title="是否允许为空">为null?</th><th>扩展</th><th>说明</th></tr></thead>');
		array_push($html, '<tbody>');
		foreach ( $table['columns'] as $c => $column ) {
			$key_class = '';
			if ( $column['key']=='MUL' ) {
				$key_class = ' class="warning"';
			}
			if ( $column['key']=='PRI' ) {
				$key_class = ' class="info"';
			}
			$comment = util_safe_encoding($column['comment']);
			array_push($html, '<tr'.$key_class.'><td>'.$column['field'].'</td><td>'.$column['type'].'</td><td>'.$column['key'].'</td><td>'.$column['null'].'</td><td>'.$column['extra'].'</td><td>'.$comment.'</td></tr>');
		}
		array_push($html, '</tbody>');
		array_push($html, '</table>');
		array_push($html, '</div>');

		// 索引列表
		$_indexes = [];
		foreach ( $table['indexes'] as $_i => $index ) {
			$index_name = $index['key_name'];
			if ( !array_key_exists($index_name, $_indexes) ) {
				$_indexes[$index_name] = [
					'name' => $index_name,
					'unique' => ($index['non_unique']==0) ? true : false,
					'type' => $index['index_type'],
					'fields' => [],
					'comment' => util_safe_encoding($index['comment']),
				];
			}
			$_indexes[$index_name]['fields'][$index['seq_in_index']] = $index['column_name'];
		}
		$_indexes = array_values($_indexes);
		array_push($html, '<div>');
		array_push($html, '<table class="table table-hover table-bordered">');
		array_push($html, '<thead><tr><th>#</th><th>索引名称</th><th>类型</td><th title="是否是唯一键">唯一</th><th>字段</th><th>说明</th></tr></thead>');
		array_push($html, '<tbody>');
		foreach ( $_indexes as $_i => $index ) {
			ksort($index['fields']);
			$fields = implode(',', array_values($index['fields']));
			array_push($html, '<tr><td>'.($_i+1).'</td><td>'.$index['name'].'</td><td>'.$index['type'].'</td><td>'.($index['unique']?'是':'不是').'</td><td>'.$fields.'</td><td>'.$index['comment'].'</td></tr>');
		}
		array_push($html, '</tbody>');
		array_push($html, '</table>');
		array_push($html, '</div>');

		// 建表脚本
		array_push($html, '<figure class="highlight"><pre><code class="language-sql" data-lang="sql">');
		array_push($html, $table['scripts']);
		array_push($html, '</code></pre></figure>');
		// 绘制一个数据表的相关信息 结束
	}
	array_push($html, '</div>');
	array_push($html, '</div><!-- end of col-md-9 -->');

	// 右侧数据表快速导航
	array_push($html, '<div class="col-md-3" role="complementary"><nav class="bs-docs-sidebar hidden-print hidden-xs hidden-sm"><ul class="nav bs-docs-sidenav">');
	foreach ( $tables as $i => $table ) {
		$table_id = 'id_'.trim($table['name']);
        array_push($html, '<li><a href="#'.$table_id.'">'.($i+1).'. '.$table['name'].'</a></li>');
	}
	array_push($html, '</ul></nav></div><!-- end of col-md-3 -->');

	array_push($html, '</div><!-- end row -->');
	array_push($html, '</div><!-- end container -->');

	// 页尾
	array_push($html, '<footer class="bs-docs-footer"><div class="container">');
    array_push($html, '<ul class="bs-docs-footer-links"><li><a href="#"><i class="glyphicon glyphicon-star"></i> 代码仓库</a></li></ul>');
    array_push($html, '<p>用于快速查看已建数据库中数据表的数据字典</p>');
    array_push($html, '<p>本项目源码受 <a rel="license" href="https://github.com/twbs/bootstrap/blob/master/LICENSE" target="_blank">MIT</a>开源协议保护，文档受 <a rel="license" href="https://creativecommons.org/licenses/by/3.0/" target="_blank">CC BY 3.0</a> 开源协议保护。</p>');
    array_push($html, '</div></footer>');

    // 引用脚本
	array_push($html, '<script src="https://cdn.jsdelivr.net/npm/jquery@1.12.4/dist/jquery.min.js"></script>');
	array_push($html, '<script>window.jQuery || document.write(\'<script src="https://v3.bootcss.com/assets/js/vendor/jquery.min.js"><\/script>\')</script>');
	array_push($html, '<script src="https://cdn.jsdelivr.net/npm/bootstrap@3.3.7/dist/js/bootstrap.min.js"></script>');
	array_push($html, '<script src="https://v3.bootcss.com/assets/js/docs.min.js"></script>');
	array_push($html, '<!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->');
	array_push($html, '<script src="https://v3.bootcss.com/assets/js/ie10-viewport-bug-workaround.js"></script>');

	array_push($html, '</body></html>');
	return implode(PHP_EOL, $html);
}


// 自定义数据库访问参数
function html_custom_dbaccess() {
	$html = [];
	array_push($html, '<form action="?t=save_custom_dbaccess" method="post">');
	array_push($html, '<input name="db[host]" value="127.0.0.1">');
	array_push($html, '<input name="db[user]" value="username">');
	array_push($html, '<input name="db[pass]" value="password">');
	array_push($html, '<input name="db[port]" value="3306">');
	array_push($html, '<input name="db[name]" value="test">');
	array_push($html, '<input name="db[xset]" value="utf-8">');
	array_push($html, '<input type="submit" value="access!">');
	array_push($html, '</form>');
	return implode(PHP_EOL, $html);
}

function post_save_custom_dbaccess() {
	global $config_cookie_name, $dbconfig;
	$form = $_POST;
	if ( !isset($form['db']) || !is_array($form['db']) ) {
		// error
		html_error('表单数据不完整');
	}

	if ( !isset($form['db']['type']) ) {
		$form['db']['type'] = 'mysql';
	}

	$dbconfig = $form['db'];
	$checked = check_db_link();
	if ( !$checked ) {
		html_error('数据库访问参数验证失败');
	}

	// $cfg = http_build_query($dbconfig);
	$cfg = json_encode($dbconfig);
	$expire = time()+3600;
	$path = '';
	$domain = '';
	$secure = false;
	$httponly = true;
	$ret = setcookie($config_cookie_name, $cfg, $expire, $path, $domain, $secure, $httponly);
	if ( $checked && $ret ) {
		header('Location: ?');
	}
	exit;
}

/*
$sql = 'show tables';
$list = $link->query($sql);
var_dump($list);
echo str_repeat('- - - ', 20), PHP_EOL;

$sql = "SHOW TABLE STATUS LIKE 'answers';";
$ret = $link->query($sql);
var_dump($ret);
echo str_repeat('- - - ', 20), PHP_EOL;

$sql = "SHOW CREATE TABLE `answers`;";
$ret = $link->query($sql);
var_dump($ret[0]['Create Table']);
echo str_repeat('- - - ', 20), PHP_EOL;

$sql = "SHOW FULL COLUMNS FROM `answers`;";
$ret = $link->query($sql);
var_dump($ret);
echo str_repeat('- - - ', 20), PHP_EOL;

$sql = "SHOW INDEX FROM `answers`;";
$ret = $link->query($sql);
var_dump($ret);
echo str_repeat('- - - ', 20), PHP_EOL;
exit;
*/



function util_safe_encoding( $string, $outEncoding='UTF-8' ) {    
	$encoding = "UTF-8";
	for ( $i=0; $i<strlen($string); $i++ ) {
		if ( ord($string{$i})<128 )    
			continue;
		
		if ( (ord($string{$i})&224)==224 ) {
			//第一个字节判断通过
			$char = $string{++$i};
			if ( (ord($char)&128)==128) {
				//第二个字节判断通过
				$char = $string{++$i};
				if ( (ord($char)&128)==128 ) {
					$encoding = 'UTF-8';
					break;
				}
			}
		}

		if((ord($string{$i})&192)==192) {    
			//第一个字节判断通过
			$char = $string{++$i};
			if ( (ord($char)&128)==128 ) {
				// 第二个字节判断通过
				$encoding = 'GB2312';
				break;
			}
		}
	}
		 
	if(strtoupper($encoding) == strtoupper($outEncoding))
		return $string;
	else
		return iconv($encoding,$outEncoding,$string);
}

/*
function characet_convert($data){
   if( !empty($data) ){
      $fileType = mb_detect_encoding($data , array('UTF-8','GBK','LATIN1','BIG5' , 'UTF-16LE', 'UTF-16BE', 'ISO-8859-1')) ;
      if( $fileType != 'UTF-8'){
         $data = mb_convert_encoding($data ,'utf-8' , $fileType);
      }
   }
   return $data;
}
*/

function util_isajax() {
	$result = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest' ? true : false;
	return $result;
}


// 验证配置参数中的数据库是否可以访问
function check_db_link() {
	global $dbconfig, $link;
	try {
		// 尝试连接数据库
		$link = connect_to_mysql($dbconfig);
	} catch (Exception $e) {
		$msg = $e->getMessage();
		L('[ERROR] '.$e->getMessage());
		return false;
	}
	return true;
}

function connect_to_mysql($conf=false) {
	if ( class_exists('PDO') ) {
		// echo 'found PDO extension', PHP_EOL;
		if ( in_array('mysql', PDO::getAvailableDrivers(), true) ) {
			// echo 'found PDO mysql';
			// var_dump($conf);
			return new PdomysqlDao($conf);
		} else {
			// echo 'not fount PDO msyql';
			return false;
		}
	}
	if ( class_exists('mysqli') ) {
		echo 'found mysqli extension', PHP_EOL;
		return true;
	}
	if ( function_exists('mysql_connect') ) {
		echo 'found mysql module', PHP_EOL;
		return true;
	}
	return false;
}

class Dao {
	static protected $dao = null;
	static protected $config = [];

	public function __construct($config) {

	}
	protected function checkConfig($c) {
		$_linkstr = [];
		if ( !isset($c['type']) || trim($c['type'])=='' ) {
			return false;
		}
		$type = $c['type'] = trim($c['type']);

		if ( !isset($c['host']) || trim($c['host'])=='' ) {
			return false;
		}
		$host = $c['host'] = trim($c['host']);
		array_push($_linkstr, 'host='.$host);
		if ( !isset($c['user']) || trim($c['user'])=='' ) {
			return false;
		}
		$user = $c['user'] = trim($c['user']);
		$pass = $c['pass'] = isset($c['pass']) && trim($c['pass'])!='' ? trim($c['pass']) : '';

		$port = $c['port'] = isset($c['port']) && intval($c['port'])>0 ? intval($c['port']) : false;
		if ( $port ) {
			array_push($_linkstr, 'port='.$port);
		}

		$name = $c['name'] = isset($c['name']) && trim($c['name'])!='' ? trim($c['name']) : false;
		if ( $name ) {
			array_push($_linkstr, 'dbname='.$name);
		}
		
		$xset = $c['xset'] = isset($c['xset']) ? trim($c['xset']) : false;
		if ( $xset ) {
			if ( $xset=='utf-8' ) {
				array_push($_linkstr, 'charset=utf8');
			} else {
				array_push($_linkstr, 'charset='.$xset);
			}
		} else {
		}
		
		$dsn = $type.':'.implode(';',$_linkstr).';';
		// var_dump('dsn', $dsn);
		$c['dsn'] = $dsn;
		self::$config = $c;
		return true;
	}
}
class PdomysqlDao extends Dao {
	public function __construct($config) {
		if ( is_null(self::$dao) ) {
			if ( !$this->checkConfig($config) ) {
				return false;
			}
			$_cfg = self::$config;
			$opts = [PDO::ATTR_PERSISTENT=>true];
			self::$dao = new PDO($_cfg['dsn'], $_cfg['user'], $_cfg['pass'], $opts);
			// var_dump('@PdomysqlDao Create Pdo Connection', $_cfg);
		}

		return self::$dao;
	}

	public function query($sql) {
		$stmt = self::$dao->query($sql, PDO::FETCH_ASSOC);
		$result = $stmt->fetchAll();
		return $result;
	}
}
class MysqliDao extends Dao {}
class MysqlDao extends Dao {}

// 获取数据库表列表
function get_tables_info() {
	global $link;

	$sql = 'show tables;';
	$list = [];
	$ret = $link->query($sql);
	foreach ( $ret as $i => $rec ) {
		$table_info = [];
		foreach ( $rec as $c => $v ) {
			$table_name = trim($v);

			// 数据表基本信息
			$sql = "SHOW TABLE STATUS LIKE '{$table_name}';";
			$_r = $link->query($sql);
			$table_info['name'] = $table_name;
			$table_info['comment'] = trim($_r[0]['Comment']);
			$table_info['engine'] = trim($_r[0]['Engine']);
			$table_info['rows'] = intval($_r[0]['Rows']);
			$table_info['nextid'] = intval($_r[0]['Auto_increment']);
			$table_info['createtime'] = $_r[0]['Create_time'];
			$table_info['collation'] = intval($_r[0]['Collation']);

			// 建表脚本
			$sql = "SHOW CREATE TABLE `{$table_name}`;";
			$_ret = $link->query($sql);
			$table_info['scripts'] = $_ret[0]['Create Table'];

			// 数据表字段信息
			$columns = [];
			$sql = "SHOW FULL COLUMNS FROM `{$table_name}`;";
			$_ret = $link->query($sql);
			foreach ( $_ret as $_i => $_c ) {
				$_column = [];
				foreach ( $_c as $_ci => $_cv ) {
					$_column[strtolower($_ci)] = $_cv;
				}
				array_push($columns, $_column);
			}
			$table_info['columns'] = $columns;

			// 数据表索引信息
			$indexes = [];
			$sql = "SHOW INDEX FROM `{$table_name}`;";
			$_ret = $link->query($sql);
			foreach ( $_ret as $_i => $_c ) {
				$_index = [];
				foreach ( $_c as $_ci => $_cv ) {
					$_index[strtolower($_ci)] = $_cv;
				}
				array_push($indexes, $_index);
			}
			$table_info['indexes'] = $indexes;
		}
		array_push($list, $table_info);
	}
	return $list;
}
