<?PHP
//// 에러리포트
//error_reporting(E_ALL ^ E_NOTICE);
//error_reporting(E_ALL);
//ini_set("display_errors", 1);
ob_start();
@session_start();
// 하위 페이지 보안 호출 상수 선언
define("_BB_", TRUE);
// 캐시 제거
header("Pragma: no-cache");
header("Cache-Control: no-cache,must-revalidate");
header("Access-Control-Allow-Origin: *");
header('Access-Control-Max-Age: 86400');
header('Access-Control-Allow-Headers: x-requested-with');
// header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header("Access-Control-Allow-Headers: Content-Type");
header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token, Authorization'); 

// 보안설정이나 프레임이 달라도 쿠키가 통하도록 설정
header('P3P: CP="BB_PROJECT"');

// 짧은 환경 변수
if (isset($HTTP_POST_VARS) && !isset($_POST)) {
	$_POST		= &$HTTP_POST_VARS;
	$_GET       = &$HTTP_GET_VARS;
	$_SERVER	= &$HTTP_SERVER_VARS;
	$_COOKIE	= &$HTTP_COOKIE_VARS;
	$_ENV       = &$HTTP_ENV_VARS;
	$_FILES     = &$HTTP_POST_FILES;

    if (!isset($_SESSION)) $_SESSION = &$HTTP_SESSION_VARS;
}

$ext_arr = array ('PHP_SELF', '_ENV', '_GET', '_POST', '_FILES', '_SERVER', '_COOKIE', '_SESSION', '_REQUEST',
                  'HTTP_ENV_VARS', 'HTTP_GET_VARS', 'HTTP_POST_VARS', 'HTTP_POST_FILES', 'HTTP_SERVER_VARS',
                  'HTTP_COOKIE_VARS', 'HTTP_SESSION_VARS', 'GLOBALS');

$ext_cnt = count($ext_arr);
for ($i=0; $i<$ext_cnt; $i++) {
    // GET 으로 선언된 전역변수가 있다면 unset() 시킴
    if (isset($_GET[$ext_arr[$i]])) unset($_GET[$ext_arr[$i]]);
}
//==========================================================================================================
// PHP 4.1.0 부터 지원됨
// php.ini 의 register_globals=off 일 경우
@extract($_GET);
@extract($_POST);
@extract($_SERVER);

error_reporting(E_ALL ^ E_NOTICE ^ E_DEPRECATED);
//ini_set('display_errors', '1');

$mobile_agent = '/(iPod|iPhone|Android|BlackBerry|SymbianOS|SCH-M\d+|Opera Mini|Windows CE|Nokia|SonyEricsson|webOS|PalmOS)/';
$device = 'W';

$userAgent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';

//$ipaddr = 0;
//$ref_url = 0;
//$query_string = 0;
//$self = 0;
//$ref = 0;
//$path = '..';

if(preg_match($mobile_agent, $_SERVER['HTTP_USER_AGENT'])) $device = 'M';
//if( strlen( $ipaddr ) == 0 ) $ipaddr = $_SERVER["REMOTE_ADDR"];
//if( strlen( $ref_url ) == 0 ) $ref_url = $_SERVER["HTTP_REFERER"];
//if( strlen( $query_string ) == 0 ) $query_string = $_SERVER["QUERY_STRING"];
//if( strlen( $self ) == 0 ) $self = $_SERVER['PHP_SELF'];


//$action_param = array( 'ipaddr'=>$ipaddr, 'referer'=>$ref_url, 'ref'=>$ref, 'self'=> $self, 'param'=>$query_string );


$root_f = "..";
// include_once realpath($_SERVER["DOCUMENT_ROOT"]."")."/".$root_f."/lib/config.php";
// include_once realpath($_SERVER["DOCUMENT_ROOT"]."")."/".$root_f."/lib/config.db.php";

// include_once realpath($_SERVER["DOCUMENT_ROOT"]."")."/".$root_f."/lib/function.php";
// include_once realpath($_SERVER["DOCUMENT_ROOT"]."")."/".$root_f."/lib/data.function.php";

include_once realpath($_SERVER["DOCUMENT_ROOT"]."")."/lib/config.php";
include_once realpath($_SERVER["DOCUMENT_ROOT"]."")."/lib/config.db.php";

include_once realpath($_SERVER["DOCUMENT_ROOT"]."")."/lib/function.php";
include_once realpath($_SERVER["DOCUMENT_ROOT"]."")."/lib/data.function.php";


?>