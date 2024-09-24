<?php

if (PHP_VERSION >= '5.1.0') {
    //if (function_exists("date_default_timezone_set")) date_default_timezone_set("Asia/Seoul");
    date_default_timezone_set("Asia/Seoul");
}

$http = 'http' . ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']=='on') ? 's' : '') . '://';
$host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME'];
if(isset($_SERVER['HTTP_HOST']) && preg_match('/:[0-9]+$/', $host))	$host = preg_replace('/:[0-9]+$/', '', $host);
$port = $_SERVER['SERVER_PORT'] != 80 ? ':'.$_SERVER['SERVER_PORT'] : '';

define('_BB_DEV_MODE',					false);
define('_BB_CONNECT_PATH',				_BB_DEV_MODE ? "/" : "" );

//define('_BB_COOKIE_DOMAIN',				'');
define('_BB_HOST',						$_SERVER['SERVER_NAME']);
define('_BB_DOMAIN',					$http.$host);
define('_BB_HTTPS_DOMAIN',				$http.$host.$port."");
//
//
define('_BB_ADMIN_DIR',					'');
define('_BB_DATA_DIR',					'data');
define('_BB_SESSION_DIR',				'session');
define('_BB_IMG_DIR',					'img');
define('_BB_JS_DIR',					'js');
define('_BB_CSS_DIR',					'css');
define('_BB_ADMIN_SKIN_DIR',			'skin');
define('_BB_ADMIN_BBS_DIR',				'bbs');

define('_BB_PATH',						realpath($_SERVER["DOCUMENT_ROOT"]._BB_CONNECT_PATH));
define('_BB_ADMIN_PATH',				realpath($_SERVER["DOCUMENT_ROOT"]._BB_CONNECT_PATH.""._BB_ADMIN_DIR));

define('_BB_DATA_PATH',					_BB_ADMIN_PATH.'/'._BB_DATA_DIR);
define('_BB_SESSION_PATH',				_BB_PATH.'/'._BB_SESSION_DIR);
define('_BB_LIB_PATH',					_BB_PATH."/lib");
define('_BB_CSS_PATH',					_BB_PATH.'/'._BB_CSS_DIR);
define('_BB_JS_PATH',					_BB_PATH."/js");
//
//define('_BB_ADMIN_BBS_PATH',			_BB_ADMIN_PATH.'/'._BB_ADMIN_BBS_DIR);
//define('_BB_ADMIN_SKIN_PATH',			_BB_ADMIN_BBS_PATH.'/'._BB_ADMIN_SKIN_DIR);
//define('_BB_ADMIN_SKIN_BOARD_PATH',     _BB_ADMIN_SKIN_PATH.'/board');
//
define('_BB_URL_PATH',					_BB_HTTPS_DOMAIN._BB_CONNECT_PATH);

//define('_BB_SESSION_URL_PATH',			_BB_URL_PATH.'/'._BB_SESSION_DIR);
define('_BB_IMG_URL_PATH',				_BB_URL_PATH.'/'._BB_IMG_DIR);
define('_BB_LIB_URL_PATH',				_BB_URL_PATH."/lib");
define('_BB_CSS_URL_PATH',				_BB_URL_PATH.'/'._BB_CSS_DIR);
define('_BB_JS_URL_PATH',				_BB_URL_PATH.'/'._BB_JS_DIR);
define('_BB_PAGES_URL_PATH',			_BB_URL_PATH."/pages");

define('_BB_ADMIN_URL_PATH',			_BB_URL_PATH);
define('_BB_ADMIN_DATA_URL_PATH',		_BB_ADMIN_URL_PATH.'/data');

//define('_BB_ADMIN_URL_IMG_PATH',		_BB_ADMIN_URL_PATH."/img");
define('_BB_ADMIN_CSS_URL_PATH',		_BB_ADMIN_URL_PATH."/phptemplate/css");
define('_BB_ADMIN_JS_URL_PATH',			_BB_ADMIN_URL_PATH."/phptemplate/js");
define('_BB_ADMIN_LIB_URL_PATH',		_BB_ADMIN_URL_PATH."/lib");
define('_BB_ADMIN_API_URL_PATH',		_BB_ADMIN_URL_PATH."/api");
define('_BB_ADMIN_BBS_URL_PATH',		_BB_ADMIN_URL_PATH."/bbs");
//define('_BB_ADMIN_BBS_LIB_URL_PATH',	_BB_ADMIN_BBS_URL_PATH."/lib");
//define('_BB_ADMIN_BBS_SKIN_URL_PATH',	_BB_ADMIN_BBS_URL_PATH."/skin");
define('_BB_ADMIN_IMG_URL_PATH',		_BB_ADMIN_URL_PATH."/img");
define('_BB_ADMIN_MEMBER_URL_PATH',		_BB_ADMIN_URL_PATH."/bbs/member");
define('_BB_ADMIN_MANAGED_URL_PATH',	_BB_ADMIN_URL_PATH."/managed");
define('_BB_ADMIN_LOGIN_URL_PATH',		_BB_ADMIN_MEMBER_URL_PATH.'/login');
define('_BB_ADMIN_LOGOUT_URL_PATH',		_BB_ADMIN_MEMBER_URL_PATH.'/logout');


define('_BB_SERVER_TIME',    time());
define('_BB_TIME_YMDHIS',    date('Y-m-d H:i:s', _BB_SERVER_TIME));
define('_BB_TIME_YMD',       substr(_BB_TIME_YMDHIS, 0, 10));
define('_BB_TIME_HIS',       substr(_BB_TIME_YMDHIS, 11, 8));

define('_BB_ESCAPE_PATTERN',  '/(and|or).*(union|select|insert|update|delete|from|where|limit|create|drop).*/i');
define('_BB_ESCAPE_REPLACE',  '');


define('_BB_APP_DATA_PATH',_BB_ADMIN_PATH."/../app/data/");

//$protocol = isset($_SERVER["HTTPS"]) ? 'https' : 'http';
//$host = $_SERVER['HTTP_HOST'];
//
//$script_file = str_replace("index.php","",$action_param['self']);
//$param = $action_param['param'] == "" ? "" : "?".$action_param['param'];
//if ($_SERVER['HTTPS'] != "on"){
//	$url = "https://".$host.$script_file.$param;
//	echo "<script>location.href='$url';</script>";
//}


$BB_DATE = date('YmdHis');
$BB_IPADDR = $_SERVER['REMOTE_ADDR'];

if( isset($zx) ){ define("IS_DEBUG", true); }else{ define("IS_DEBUG", false); }
define("IS_TRACE", true);

?>