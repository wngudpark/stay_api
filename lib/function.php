<?php


//******************************************* 유틸리티 함수 ***************************************//

	function randomString($length = 10) {
		$chars = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXTZabcdefghiklmnopqrstuvwxyz';
		$randomString = '';
		
		for ($i = 0; $i < $length; $i++) {
			$randomString .= $chars[rand(0, strlen($chars) - 1)];
		}
		
		return $randomString;
	}

	function getClientIp() {
		if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
			return $_SERVER['HTTP_CLIENT_IP'];
		} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			$ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
			return trim($ips[0]);
		} else {
			return $_SERVER['REMOTE_ADDR'];
		}
	}

	function apiLog($message, $secretKey, $innerData, $DB) {
		$client_ip = getClientIp();
		$sql = "INSERT INTO api_log (receive_date, receive_ip, receive_key, receive_data, msg) VALUES(NOW(), ?, ?, ?, ?)";
		$result = $DB->prepare($sql);

		if (is_array($innerData)) {
			$innerData = json_encode($innerData, JSON_UNESCAPED_UNICODE);
		}

		header("Content-Type: application/json; charset=utf-8");
		$result->bind_param("ssss", $client_ip, $secretKey, $innerData, $message);
		$result->execute();
		$result->close();
	}

	//api 메세지 전송
	function respondWithJson($message = 'success', $status = 'success', $secretKey = null, $innerData = null, $DB = null) {
		$response = [
			'message' => $message,
			'status'  => $status,
		];

		if ($DB) {
			apiLog($message, $secretKey, $innerData, $DB);
		}
	
		header("Content-Type: application/json");
		echo json_encode($response);
	
		exit;
	}

	//jwt, jsonData
	function getJson($key ,$DB){
		$secretKey = $key;
		$headers = getallheaders();
		$jsonData = file_get_contents('php://input');
		$data = json_decode($jsonData, true);
		$innerData = json_decode($data['data'], true);

		error_log(print_r($data, true));
		error_log(print_r($innerData, true));

		if (!isset($headers['Authorization'])) {
			respondWithJson('Missing Authorization Header', 'error', $secretKey, $innerData, $DB);
		}
	
		list(, $jwt) = explode(' ', $headers['Authorization']);
		if ($jwt !== $secretKey) {
			respondWithJson('Invalid JWT', 'error', $secretKey, $innerData, $DB);
		}
	
		if (empty($jsonData)) {
			respondWithJson("No JSON data provided...", 'error', $secretKey, $innerData, $DB);
		}
	
		if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
			respondWithJson('Invalid JSON!', 'error', $secretKey, $innerData, $DB);
		}
	
		if ($innerData === null && json_last_error() !== JSON_ERROR_NONE) {
			respondWithJson('Invalid JSON in data field!', 'error', $secretKey, $innerData, $DB);
		}

		if (!isset($innerData['action']) || !in_array($innerData['action'], ['insert', 'update', 'delete'])) {
			respondWithJson("'action' key is missing or has an invalid value. It must be one of 'insert', 'update', or 'delete'!", 'error', $secretKey, $innerData, $DB);
		}

		if (!(isset($innerData['reservations']) && is_array($innerData['reservations'])) && !(isset($innerData['survey']) && is_array($innerData['survey']))) {
			respondWithJson("The 'reservations' or 'survey' key is missing or is not an array!", 'error', $secretKey, $innerData, $DB);
		}
	
		return [$secretKey, $headers, $jsonData, $data, $innerData];
	}

	//json 데이터 바인딩 후 쿼리 실행
	function rf_pdo_query($sql, $params, $DB) {
		$result = $DB->prepare($sql);
		foreach ($params as $param => $value) {
			$result->bindValue($param, $value);
		}
		if (!$result->execute()) {
			echo json_encode(array('code'=>1, 'reason'=>'Error['.mysqli_errno($DB).']'.mysqli_error($DB)) );
			exit;
		} else {
			return $result;
		}
	}

	function execute_query($result, $db){
		if (!$result->execute()) {
			echo json_encode(array('code' => 1, 'reason' => 'Error[' . mysqli_errno($db) . ']' . mysqli_error($db)));
			exit;
		}
	}

	// 기본 셀렉트 쿼리
	function rf_mysql_query( $sql, $DB, $func_name="" ){
		$result = sql_query($sql);
		if (!$result) {
			echo error_json( array('code'=>1, 'reason'=>$func_name.' Error['.mysqli_errno($DB).']'.mysqli_error($DB)) ); //'reason'=>$func_name.' Error') );
			exit;
		}else{
			return $result;
		}
	}

	// 여러행 가져오기
	function rf_mysql_arr($sql, $DB, $func_name=""){
		$arr = Array();
		$result = @rf_mysql_query($sql, $DB, $func_name);
		while ($row = mysqli_fetch_assoc($result)) {
			$arr[] = $row;
		}
		return $arr;
	}

	// 한개행 가져오기
	function rf_mysql_row($sql, $DB, $func_name=""){
		
		return mysqli_fetch_assoc(rf_mysql_query($sql, $DB, $func_name));
	}

	// mysql_real_escape_string 변환 함수
	function ESC($str, $db_res){
		return mysqli_real_escape_string($db_res,$str);
	}

	// 에러 json
	function error_json( $param = array() ){
		$result['basic'] = $param;
		echo json_encode( $result );
		exit;
	}

	//서버통신
	function https_post($uri, $postdata = null) {
	  $ch = curl_init($uri);
	  curl_setopt($ch, CURLOPT_POST, true);
	  if( $postdata ) curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
	  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	  $result = curl_exec($ch);
	  curl_close($ch);

	  return $result;
	}

	// mysql_query 와 mysql_error 를 한꺼번에 처리
	function sql_query($sql, $error=TRUE)
	{
		global $DB;
		if ($error)
			$result = @mysqli_query($DB, $sql) or die("<p>$sql<p>" . mysqli_errno($DB) . " : " .  mysqli_error($DB) . "<p>error file : $_SERVER[PHP_SELF]");
		else
			$result = @mysqli_query($DB,$sql);
		
		return $result;
	}

	// 쿼리를 실행한 후 결과값에서 한행을 얻는다.
	function sql_fetch($sql, $error=TRUE)
	{
		global $DB;
		$result = sql_query($sql, $error);
		//print_r($result);
		if ($error)
			$row = @sql_fetch_array($result) or die("<p>$sql<p>" . mysqli_errno($DB) . " : " .  mysqli_errno($DB) . "<p>error file : $_SERVER[PHP_SELF]");
		else
			$row = sql_fetch_array($result);
		
		return $row;
	}


	// 결과값에서 한행 연관배열(이름으로)로 얻는다.
	function sql_fetch_array($result)
	{
		$row = @mysqli_fetch_assoc($result);
		return $row;
	}


	// $result에 대한 메모리(memory)에 있는 내용을 모두 제거한다.
	// sql_free_result()는 결과로부터 얻은 질의 값이 커서 많은 메모리를 사용할 염려가 있을 때 사용된다.
	// 단, 결과 값은 스크립트(script) 실행부가 종료되면서 메모리에서 자동적으로 지워진다.
	function sql_free_result($result)
	{
		return mysql_free_result($result);
	}

//	function sql_password($value)
//	{
//		// mysql 4.0x 이하 버전에서는 password() 함수의 결과가 16bytes
//		// mysql 4.1x 이상 버전에서는 password() 함수의 결과가 41bytes
//		$row = sql_fetch(" select CONCAT('*', UPPER(SHA1(UNHEX(SHA1('$value'))))) as pass ");
//		return $row['pass'];
//	}

	
	function sql_password($value){
		$_BRIGHTBELLPASS = new _BRIGHTBELLPASS();
		$password = base64_encode($_BRIGHTBELLPASS->encryptToken($value));
		return $password;
	}

	function get_password($hash){
		$_BRIGHTBELLPASS = new _BRIGHTBELLPASS();
		$password = $_BRIGHTBELLPASS->decryptToken($hash);
		return $password;
	}

	//비밀번호 비교 AES256
	function check_password_aes256($pass, $hash){
		$_BRIGHTBELLPASS = new _BRIGHTBELLPASS();
		$password = base64_encode($_BRIGHTBELLPASS->encryptToken($pass));
		
		//echo $password;
		
		return ($password == $hash);
	}




	//서버통신 (GET통신: http://domain.com?a=aaa&b=aaa, null, false, POST통신 url, array('a'=>'aaa','b'=>'bbb')
	function server_comm($uri, $postdata = null, $post = true, $timeout=10) {
		$ch = curl_init($uri);
		if( $post ){
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
		}
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout); // 시간제한:초(제한 없음 : 0 )
		$result = curl_exec($ch);
		curl_close($ch);

		// 페이스북에서 리턴되는uid 길이가 정수형을 넘어서 스트링으로 변경
		$rtn = preg_replace('/"uid":(\d+)/', '"uid":"\1"', $result );
		//debug( $rtn ); exit;
		return $rtn;
	}

	function debug($data){
		if( IS_DEBUG ){
			echo '<pre>';
			echo print_r($data, true);
			//echo var_dump($data);
			echo '</pre>';
		}
	}

	function cvt_int( $value ){
		return ( $value !== null ) ? (int)$value : null;
	}

	// 기본 로그 저장 함수
	function save_log( $table="log", $action_code="", $detail_code="", $action_param="", $action_desc="", $referer="", $ipaddr="", $DB ){
		if( IS_TRACE ){
			//debug( $user );
			//if( $detail_code == "" ) $detail_code = $action_code;
			$sql = "INSERT INTO ".$table." SET
					action_code='".ESC($action_code,$DB)."'
					, detail_code='".ESC($detail_code,$DB)."'
					, action_param='".ESC( json_encode($action_param), $DB)."'
					, action_desc='".substr( ESC($action_desc,$DB), 0, 255)."'
					, referer='".substr( ESC($referer,$DB), 0, 100)."'
					, ipaddr='".ESC($ipaddr,$DB)."'";
			debug( $sql );
			rf_mysql_query($sql, $DB);
		}
	}


	// 자바스크립트 대응함수
	// http://phpschool.com/gnuboard4/bbs/board.php?bo_table=tipntech&wr_id=60918&sca=&sfl=wr_subject%7C%7Cwr_content&stx=unescape&sop=and
   /*
    * javascript escape 대응함수
    */
    function unescape($text)
    {
      return urldecode(preg_replace_callback('/%u([[:alnum:]]{4})/', create_function(
                '$word',
                'return iconv("UTF-16LE", "UTF-8", chr(hexdec(substr($word[1], 2, 2))).chr(hexdec(substr($word[1], 0, 2))));'
                ), $text));
    }

    /*
    * javascript escape 대응함수
    */
    function escape($str)
    {
      $len = strlen($str);
      for($i=0,$s='';$i<$len;$i++) {
          $ck = substr($str,$i,1);
          $ascii = ord($ck);
          if($ascii > 127) $s .= '%u'.toUnicode(substr($str, $i++, 2));
          else $s .= (in_array($ascii, array(42, 43, 45, 46, 47, 64, 95))) ? $ck : '%'.strtoupper(dechex($ascii));
      }
      return $s;
    }

    function toUnicode($word) {
      $word = iconv('UTF-8', 'UTF-16LE', $word);
      return strtoupper(str_pad(dechex(ord(substr($word,1,1))),2,'0',STR_PAD_LEFT).str_pad(dechex(ord(substr($word,0,1))),2,'0',STR_PAD_LEFT));
    }



    #paging
    function handlePage($PHP_SELF, $totalRecord,$recordPerPage,$pagePerBlock,$currentPage,$param=""){
        global $sid_param;
        // 전체레코드,  페이지당 레코드수(10) , 블럭당페이지수(10), 현재페이지
		echo  $totalRecord;
		if($param !=""){
			$param = "&".$param;
		}
        $totalNumOfPage = ceil($totalRecord/$recordPerPage); //16page
        $totalNumOfBlock = ceil($totalNumOfPage/$pagePerBlock); //2block
        $currentBlock = ceil($currentPage/$pagePerBlock); // 1page

        $startPage = ($currentBlock-1)*$pagePerBlock+1;  // 1page
        $endPage = $startPage+$pagePerBlock -1; // 10page
        if($endPage > $totalNumOfPage) $endPage = $totalNumOfPage;

        //NEXT,PREV 존재 여부
        $isNext = false;
        $isPrev = false;

		$isNextBlock = false;
        $isPrevBlock = false;

        if($currentBlock < $totalNumOfBlock){
			$isNextBlock = true;

		}
        if($currentBlock > 1){ 
			$isPrevBlock = true;
		}

        if($totalNumOfBlock == 1){
            $isNextBlock = false;
            $isPrevBlock = false;
        }
		
		if($startPage == $currentPage && !$isPrevBlock){
			$isPrev = false;
		} else {
			$isPrev = true;
		}

		if($endPage == $currentPage && !$isNextBlock){
			$isNext = false;
		} else {
			$isNext = true;
		}

		
		if($isPrevBlock){
			$goPrevPage = $startPage-$pagePerBlock; // 11page
			echo "<a href='$PHP_SELF?page=$goPrevPage$param' class='btns prev2'>처음으로</a>";
		} else {
			echo "<a class='btns prev2 disabled' href='javascript:void(0)'>처음으로</i></a>";
		}
		
        if($isPrev){
            $goPrev = $currentPage-1;
			echo "<a class='btns prev ' href='$PHP_SELF?page=$goPrev$param'>이전</a>";
        } else {
			echo "<a class='btns prev disabled' href='javascript:void(0)'>이전</a>";
		}


		echo "<span class='num'>";
        for($i=$startPage;$i<=$endPage;$i++){
            if( $i == $currentPage ){
				$cur = "   ";
				$href = "javascript:void(0)";
				echo "<strong>".$i."</strong>";
			} else {
				$cur = " ";
				$href = "$PHP_SELF?page=$i$param";
				echo "<a ".$cur." href='$href'>".$i."</a>";
			}
            
			
        }
		echo "</span>";

		
        if($isNext){
			$goNext = $currentPage+1;
			echo "<a class='btns next' href='$PHP_SELF?page=$goNext$param'>다음</a>";
        } else {
			echo "<a class='btns next disabled' href='javascript:void(0)'><i class='icon-arr-right'></i></a>";
		}
		if($isNextBlock){
			$goNextPage = $startPage+$pagePerBlock; // 11page
			echo "<a class='btns next2' href='$PHP_SELF?page=$goNextPage$param'>마지막으로</a>";
		} else {
			echo "<a class='btns next2 disabled' href='javascript:void(0)'>마지막으로</a>";
		}
    }
	
	function removeParameterFromUrl($url, $key)
	{
		$parsed = parse_url($url);
		$path = $parsed['path'];
		unset($_GET[$key]);
		if(!empty(http_build_query($_GET))){
		  return $path .'?'. http_build_query($_GET);
		} else return $path;
	}
	// parameter all get
	// echo _param_get();
	
	function isJson($string) {
		json_decode($string);
		return (json_last_error() == JSON_ERROR_NONE);
	}

	function _param_get($delete_key = '',$query = NULL,$char = NULL,$method = 'GET') {
		$parameter = ($method == 'GET') ? $_GET: $_POST;
		$ret = array();
		$output = array();
		
		if ( !empty($query) ) {
			parse_str($query,$output);
			foreach(array_keys($output) as $key){
				if ( !empty($output[$key]) ) {
					$ret[$key] = $output[$key];
				} else {
					unset($parameter[$key]);
				}
				
			}

		}
		
		if($delete_key !=""){
			if(!is_array($delete_key)){
				unset($parameter[$delete_key]);
			} else {
				for ($i=0; $i < count($delete_key); $i++){
					unset($parameter[$delete_key[$i]]);
				}
			}
		}

		$param = http_build_query(array_merge($parameter, $ret));
		if ( $char != NULL && !empty($param) ) { $param = $char . $param; }
		return $param;
	}
	
// TEXT 형식으로 변환
	function get_text($str, $html=0, $restore=false)
	{
		$source[] = "<";
		$target[] = "&lt;";
		$source[] = ">";
		$target[] = "&gt;";
		$source[] = "\"";
		$target[] = "&#034;";
		$source[] = "\'";
		$target[] = "&#039;";

		if($restore)
			$str = str_replace($target, $source, $str);

		// 3.31
		// TEXT 출력일 경우 &amp; &nbsp; 등의 코드를 정상으로 출력해 주기 위함
		if ($html == 0) {
			$str = html_symbol($str);
		}

		if ($html) {
			$source[] = "\n";
			$target[] = "<br/>";
		}

		return str_replace($source, $target, $str);
	}


	function cut_str($str, $len, $suffix="…")
	{
		$arr_str = preg_split("//u", $str, -1, PREG_SPLIT_NO_EMPTY);
		$str_len = count($arr_str);

		if ($str_len >= $len) {
			$slice_str = array_slice($arr_str, 0, $len);
			$str = join("", $slice_str);

			return $str . ($str_len > $len ? $suffix : '');
		} else {
			$str = join("", $arr_str);
			return $str;
		}
	}

	function html_symbol($str){
		return preg_replace("/\&([a-z0-9]{1,20}|\#[0-9]{0,3});/i", "&#038;\\1;", $str);
	}

	// 내용을 변환
	// 내용을 변환
	function conv_content($content, $html, $filter=true)
	{
		
		if ($html)
		{
			
			$source = array();
			$target = array();

			$source[] = "//";
			$target[] = "";

			if ($html == 2) { // 자동 줄바꿈
				$source[] = "/\n/";
				$target[] = "<br/>";
			}

			// 테이블 태그의 개수를 세어 테이블이 깨지지 않도록 한다.
			$table_begin_count = substr_count(strtolower($content), "<table");
			$table_end_count = substr_count(strtolower($content), "</table");
			for ($i=$table_end_count; $i<$table_begin_count; $i++)
			{
				$content .= "</table>";
			}

			$content = preg_replace($source, $target, $content);

	//        if($filter)
	//            $content = html_purifier($content);
		}
		else // text 이면
		{
			
			// & 처리 : &amp; &nbsp; 등의 코드를 정상 출력함
			//$content = html_symbol($content);

			// 공백 처리
			//$content = preg_replace("/  /", "&nbsp; ", $content);
			$content = str_replace("  ", "&nbsp; ", $content);
			$content = str_replace("\n ", "\n&nbsp;", $content);
			//$content = str_replace("&#39;", "'", $content);

			$content = get_text($content, 1);
			$content = url_auto_link($content);
		}

		return $content;
	}

	function url_auto_link($str){
		global $g5;
		global $config;

		
		$str = str_replace(array("&lt;", "&gt;", "&amp;", "&quot;", "&nbsp;", "&#039;"), array("\t_lt_\t", "\t_gt_\t", "&", "\"", "\t_nbsp_\t", "'"), $str);
		//$str = preg_replace("`(?:(?:(?:href|src)\s*=\s*(?:\"|'|)){0})((http|https|ftp|telnet|news|mms)://[^\"'\s()]+)`", "<A HREF=\"\\1\" TARGET='{$config['cf_link_target']}'>\\1</A>", $str);
		$str = preg_replace("/([^(href=\"?'?)|(src=\"?'?)]|\(|^)((http|https|ftp|telnet|news|mms):\/\/[a-zA-Z0-9\.-]+\.[가-힣\xA1-\xFEa-zA-Z0-9\.:&#=_\?\/~\+%@;\-\|\,\(\)]+)/i", "\\1<A HREF=\"\\2\" TARGET=\"{$config['cf_link_target']}\">\\2</A>", $str);
		$str = preg_replace("/(^|[\"'\s(])(www\.[^\"'\s()]+)/i", "\\1<A HREF=\"http://\\2\" TARGET=\"{$config['cf_link_target']}\">\\2</A>", $str);
		$str = preg_replace("/[0-9a-z_-]+@[a-z0-9._-]{4,}/i", "<a href=\"mailto:\\0\">\\0</a>", $str);
		$str = str_replace(array("\t_nbsp_\t", "\t_lt_\t", "\t_gt_\t", "'"), array("&nbsp;", "&lt;", "&gt;", "&#039;"), $str);

		return $str;
	}

	// unescape nl 얻기
	function conv_unescape_nl($str)
	{
		$search = array('\\r', '\r', '\\n', '\n');
		$replace = array('', '', "\n", "\n");

		return str_replace($search, $replace, $str);
	}
	

	function get_section_key($section){
		$sectionArray = array(
			'MAIN' => 's0-0',
			'GREETINGS' => 's1-0',
			'VISION' => 's1-1',
			'PURPOSEBUSINESS' => 's1-2',
			'JUNIORCAMPUS' => 's2-0',
			'YOUNGENGINEDREAM' => 's2-1',
			'NEXTGREEN' => 's2-2',
			'HISTORY' => 's2-3',
			'PRESS' => 's3-0',
			'NEWSLETTER' => 's3-1',
			'ANNUAL' => 's3-2',
			'NEWS' => 's3-3',
			'DONATION' => 's4-0',
		);
		
		if (isset( $sectionArray[$section] )) {
			return $sectionArray[$section];
		} else {
			return 's0-0';
		}

	}

	function set_file_area($mode,$sectioncode,$filecode,$idx,$lang=""){
		global $_BB_DB_TABLE, $qstr, $DB, $BB_DATE;
		if($lang == ""){
			$lang = "ko";
		}
		$updateSql = "";
		if($mode == "d"){
			$updateSql = " delete from ".$_BB_DB_TABLE['BOARD_FILE_AREA']." where  area_idx = '$idx' and area_section = '$sectioncode' and area_lang = '$lang' ";
			rf_mysql_query($updateSql, $DB);
		} else if($mode == "i"){
			//$updateSql = " update ".$_BB_DB_TABLE['BOARD_FILE']." set bf_use_area = concat(data, '".$sectioncode."'); ";
			$sql = " select count(idx) as cnt from ".$_BB_DB_TABLE['BOARD_FILE_AREA']." where area_idx = '$idx' and area_section = '$sectioncode'  and area_lang = '$lang' ";
			$row = sql_fetch($sql);
			if($row['cnt'] == 0){
				$fileCodeJson = json_decode($filecode,true);
				$ix=0;
				foreach($fileCodeJson as $key => $value) {
					//echo 'is begin with ('.$key.')'.'<br>';
					$updateSql = " insert into ".$_BB_DB_TABLE['BOARD_FILE_AREA']." set area_order = '{$ix}' ,area_idx = '{$idx}' , area_section = '{$sectioncode}',  area_lang = '$lang' , bf_code = '{$key}', area_datetime  = '".ESC($BB_DATE, $DB)."' ";
					rf_mysql_query($updateSql, $DB);
					$ix++;
				}

			} else {
				$sql = " select bf_code from ".$_BB_DB_TABLE['BOARD_FILE_AREA']." where area_idx = '$idx' and area_section = '$sectioncode' and area_lang = '$lang' ";
				$row = sql_fetch($sql);
				//if($row['bf_code'] != $filecode){
					
					//$deleteSql = " delete from ".$_BB_DB_TABLE['BOARD_FILE_AREA']." where bf_code = '".$row['bf_code']."' and area_idx = '$idx' and area_section = '$sectioncode' ";
					$deleteSql = " delete from ".$_BB_DB_TABLE['BOARD_FILE_AREA']." where area_idx = '$idx' and area_section = '$sectioncode' and area_lang = '$lang' ";
					rf_mysql_query($deleteSql, $DB);
					$fileCodeJson = json_decode($filecode,true);
					$ix=0;
					foreach($fileCodeJson as $key => $value) {
						$updateSql = " insert into ".$_BB_DB_TABLE['BOARD_FILE_AREA']." set area_order = '{$ix}' ,area_idx = '{$idx}' , area_section = '{$sectioncode}' , area_lang = '$lang' , bf_code = '{$key}', area_datetime  = '".ESC($BB_DATE, $DB)."' ";
						rf_mysql_query($updateSql, $DB);
						$ix++;
					}
				//}
			}
		}

		if($updateSql !=""){
			//rf_mysql_query($updateSql, $DB);
		}
	}
	
	function get_data($table, $column,$value){
		global $_BB_DB_TABLE, $qstr, $DB;
		$sql = " select * from ".$_BB_DB_TABLE[$table]." where $column = '$value' order by idx ";
		$arr = null;
		$res = rf_mysql_arr($sql, $DB);
		foreach( $res as $cmt ){
			$arr[] = $cmt;
		}
		return $arr;
	}

	

	
	// 게시글에 첨부된 파일을 얻는다. (배열로 반환)
	function get_file($bf_code)
	{
		global $_BB_DB_TABLE, $qstr;

		$file['count'] = 0;
		$sql = " select * from ".$_BB_DB_TABLE['BOARD_FILE']." where bf_code = '$bf_code' order by idx ";
		$result = sql_query($sql);
		
		while ($row = sql_fetch_array($result))
		{
			
			$file['bf_no'] = $row['bf_no'];
			$file['href'] = _BB_ADMIN_BBS_URL_PATH."/download.php?bf_code=$bf_code".$qstr;
			//$file['download'] = $row['bf_download'];
			$file['path'] = _BB_DATA_URL_PATH.'/file/';
			$file['size'] = get_filesize($row['bf_filesize']);
			$file['datetime'] = $row['bf_datetime'];
			$file['source'] = addslashes($row['bf_source']);
			$file['bf_content'] = $row['bf_content'];

			if($row['bf_type'] < 1 || $row['bf_type'] > 3){
				$file['thumb_file'] = "";
			} else {
				$fn = preg_replace("/\.[^\.]+$/i", "", basename($row['bf_file']));
				$files = glob(_BB_DATA_PATH.'/file/thumb_nail/'.$fn.'*');
				if (is_array($files)) {
					foreach ($files as $filename){
						$tokens = explode('/', $filename);
						$file['thumb_file'] = $tokens[sizeof($tokens)-1];
					}
				}
			}
			
			//$file['content'] = get_text($row['BF_CONTENT']);
			//$file['view'] = view_file_link($row['bf_file'], $file['content']);
			//$file['view'] = view_file_link($row['BF_FILE'], $row['BF_WIDTH'], $row['BF_HEIGHT'], $file['CONTENT']);
			$file['file'] = $row['bf_file'];
			$file['image_width'] = $row['bf_width'] ? $row['bf_width'] : 0;
			$file['image_height'] = $row['bf_height'] ? $row['bf_height'] : 0;
			$file['image_type'] = $row['bf_type'];
			$file['type'] = $row['bf_type'];
			$file['mime'] = $row['bf_mime'];
			$file['mime_type'] = $row['bf_mime_type'];
			$file['code'] = $row['bf_code'];
			$file['count']++;
		}

		return $file;
	}

	function get_mime_type($filename) {
		$idx = explode( '.', $filename );
		$count_explode = count($idx);
		$idx = strtolower($idx[$count_explode-1]);

		$mimet = array( 
			'txt' => 'text/plain',
			'htm' => 'text/html',
			'html' => 'text/html',
			'php' => 'text/html',
			'css' => 'text/css',
			'js' => 'application/javascript',
			'json' => 'application/json',
			'xml' => 'application/xml',
			'swf' => 'application/x-shockwave-flash',
			'flv' => 'video/x-flv',

			// images
			'png' => 'image/png',
			'jpe' => 'image/jpeg',
			'jpeg' => 'image/jpeg',
			'jpg' => 'image/jpeg',
			'gif' => 'image/gif',
			'bmp' => 'image/bmp',
			'ico' => 'image/vnd.microsoft.icon',
			'tiff' => 'image/tiff',
			'tif' => 'image/tiff',
			'svg' => 'image/svg+xml',
			'svgz' => 'image/svg+xml',

			// archives
			'zip' => 'application/zip',
			'rar' => 'application/x-rar-compressed',
			'exe' => 'application/x-msdownload',
			'msi' => 'application/x-msdownload',
			'cab' => 'application/vnd.ms-cab-compressed',

			// audio/video
			'mp3' => 'audio/mpeg',
			'mp4' => 'video/mp4',
			'wmv' => 'video/wmv',
			'qt' => 'video/quicktime',
			'mov' => 'video/quicktime',

			// adobe
			'pdf' => 'application/pdf',
			'psd' => 'image/vnd.adobe.photoshop',
			'ai' => 'application/postscript',
			'eps' => 'application/postscript',
			'ps' => 'application/postscript',

			// ms office
			'doc' => 'application/msword',
			'rtf' => 'application/rtf',
			'xls' => 'application/vnd.ms-excel',
			'ppt' => 'application/vnd.ms-powerpoint',
			'docx' => 'application/msword',
			'xlsx' => 'application/vnd.ms-excel',
			'pptx' => 'application/vnd.ms-powerpoint',


			// open office
			'odt' => 'application/vnd.oasis.opendocument.text',
			'ods' => 'application/vnd.oasis.opendocument.spreadsheet',

				'apk' => 'android/apk',
				'exe' => 'windows/exe',
		);

		if (isset( $mimet[$idx] )) {
		 return $mimet[$idx];
		} else {
		 return 'application/octet-stream';
		}
	}
	function get_mime($filename) {
		$idx = explode( '.', $filename );
		$count_explode = count($idx);
		$idx = strtolower($idx[$count_explode-1]);

		return $idx;
	}
	
	function get_mime_gubun($mime){
		$mimet = array( 
			'txt' => 'text/plain',
			'htm' => 'text/html',
			'html' => 'text/html',
			'php' => 'text/html',
			'css' => 'text/css',
			'js' => 'application/javascript',
			'json' => 'application/json',
			'xml' => 'application/xml',
			'swf' => 'application/x-shockwave-flash',
			'flv' => 'video/x-flv',

			// images
			'png' => 'image/png',
			'jpe' => 'image/jpeg',
			'jpeg' => 'image/jpeg',
			'jpg' => 'image/jpeg',
			'gif' => 'image/gif',
			'bmp' => 'image/bmp',
			'ico' => 'image/vnd.microsoft.icon',
			'tiff' => 'image/tiff',
			'tif' => 'image/tiff',
			'svg' => 'image/svg+xml',
			'svgz' => 'image/svg+xml',

			// archives
			'zip' => 'application/zip',
			'rar' => 'application/x-rar-compressed',
			'exe' => 'application/x-msdownload',
			'msi' => 'application/x-msdownload',
			'cab' => 'application/vnd.ms-cab-compressed',

			// audio/video
			'mp3' => 'audio/mpeg',
			'avi' => 'video/avi',
			'mp4' => 'video/mp4',
			'wmv' => 'video/wmv',
			'qt' => 'video/quicktime',
			'mov' => 'video/quicktime',

			// adobe
			'pdf' => 'application/pdf',
			'psd' => 'image/vnd.adobe.photoshop',
			'ai' => 'application/postscript',
			'eps' => 'application/postscript',
			'ps' => 'application/postscript',

			// ms office
			'doc' => 'application/msword',
			'rtf' => 'application/rtf',
			'xls' => 'application/vnd.ms-excel',
			'ppt' => 'application/vnd.ms-powerpoint',
			'docx' => 'application/msword',
			'xlsx' => 'application/vnd.ms-excel',
			'pptx' => 'application/vnd.ms-powerpoint',
			// open office
			'odt' => 'application/vnd.oasis.opendocument.text',
			'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
				'apk' => 'android/apk',
				'exe' => 'windows/exe',
		);
	}

	// 문자열 암호화
	function get_encrypt_string($str){
		$encrypt = sql_password($str);
		return $encrypt;
	}

	function php_alert($msg,$mode=""){
		
		echo "<script>alert('".$msg."');</script>";
		
		if ($mode == "back"){
			php_history_back();
		} else if ($mode != "back" && $mode != ""){
			php_location($mode);
		}
		exit;
	}

	function php_location($url){
		echo "<script>document.location.href='".$url."';</script>";
	}

	function php_history_back(){
		echo "<script>window.history.go(-1);</script>";
	}
	
	function sql_escape_string($str){
		if(defined('_BB_ESCAPE_PATTERN') && defined('_BB_ESCAPE_REPLACE')) {
			$pattern = _BB_ESCAPE_PATTERN;
			$replace = _BB_ESCAPE_REPLACE;
			if($pattern)
				$str = preg_replace($pattern, $replace, $str);
		}

		$str = call_user_func('addslashes', $str);
		return $str;
	}


	// 비밀번호 비교
	function check_password($pass, $hash){
//		echo $pass."<br>";
//		echo get_encrypt_string($pass)."<br>";
//		echo $hash."<br>";
		$password = get_encrypt_string($pass);

		return ($password == $hash);
	}

	// 세션변수 생성
	function set_session($session_name, $value){
		if (PHP_VERSION < '5.3.0')
			session_register($session_name);
		// PHP 버전별 차이를 없애기 위한 방법
		$_SESSION[$session_name] = $value;
	}


	// 세션변수값 얻음
	function get_session($session_name){
		return isset($_SESSION[$session_name]) ? $_SESSION[$session_name] : '';
	}


	// 쿠키변수 생성
	function set_cookie($cookie_name, $value, $expire){
		global $bb;
		setcookie(md5($cookie_name), base64_encode($value), time() + $expire, '/', _BB_HOST);
	}


	// 쿠키변수값 얻음
	function get_cookie($cookie_name){
		$cookie = md5($cookie_name);
		if (array_key_exists($cookie, $_COOKIE))
			return base64_decode($_COOKIE[$cookie]);
		else
			return "";
	}

	function escape_trim($field)
	{
		$str = call_user_func('sql_escape_string', $field);
		return $str;
	}

	// 게시판의 다음글 번호를 얻는다.
	function get_next_num($table,$column){
		// 가장 작은 번호를 얻어
		$sql = " select max(".$column.") as col from $table ";
		$row = sql_fetch($sql);
		// 가장 작은 번호에 1을 빼서 넘겨줌
		return (int)($row['col'] + 1);
	}


	function goto_url($url){
		$url = str_replace("&amp;", "&", $url);
		//echo "<script> location.replace('$url'); </script>";

		if (!headers_sent())
			header('Location: '.$url);
		else {
			echo '<script>';
			echo 'location.href = "'.$url.'";';
			echo '</script>';
			echo '<noscript>';
			echo '<meta http-equiv="refresh" content="0;url='.$url.'" />';
			echo '</noscript>';
		}
		exit;
	}

	function clean_query_string($query, $amp=true){
		$qstr = trim($query);

		parse_str($qstr, $out);

		if(is_array($out)) {
			$q = array();

			foreach($out as $key=>$val) {
				$key = strip_tags(trim($key));
				$val = trim($val);

				switch($key) {
					case 'wr_id':
						$val = (int)preg_replace('/[^0-9]/', '', $val);
						$q[$key] = $val;
						break;
					case 'sca':
						$val = clean_xss_tags($val);
						$q[$key] = $val;
						break;
					case 'sfl':
						$val = preg_replace("/[\<\>\'\"\\\'\\\"\%\=\(\)\s]/", "", $val);
						$q[$key] = $val;
						break;
					case 'stx':
						$val = get_search_string($val);
						$q[$key] = $val;
						break;
					case 'sst':
						$val = preg_replace("/[\<\>\'\"\\\'\\\"\%\=\(\)\s]/", "", $val);
						$q[$key] = $val;
						break;
					case 'sod':
						$val = preg_match("/^(asc|desc)$/i", $val) ? $val : '';
						$q[$key] = $val;
						break;
					case 'sop':
						$val = preg_match("/^(or|and)$/i", $val) ? $val : '';
						$q[$key] = $val;
						break;
					case 'spt':
						$val = (int)preg_replace('/[^0-9]/', '', $val);
						$q[$key] = $val;
						break;
					case 'page':
						$val = (int)preg_replace('/[^0-9]/', '', $val);
						$q[$key] = $val;
						break;
					case 'w':
						$val = substr($val, 0, 2);
						$q[$key] = $val;
						break;
					case 'bo_table':
						$val = preg_replace('/[^a-z0-9_]/i', '', $val);
						$val = substr($val, 0, 20);
						$q[$key] = $val;
						break;
					case 'gr_id':
						$val = preg_replace('/[^a-z0-9_]/i', '', $val);
						$q[$key] = $val;
						break;
					default:
						$val = clean_xss_tags($val);
						$q[$key] = $val;
						break;
				}
			}

			if($amp)
				$sep = '&amp;';
			else
				$sep ='&';

			$str = http_build_query($q, '', $sep);
		} else {
			$str = clean_xss_tags($qstr);
		}

		return $str;
	}
	// XSS 관련 태그 제거
	function clean_xss_tags($str){
		$str = preg_replace('#</*(?:applet|b(?:ase|gsound|link)|embed|frame(?:set)?|i(?:frame|layer)|l(?:ayer|ink)|meta|object|s(?:cript|tyle)|title|xml)[^>]*+>#i', '', $str);

		return $str;
	}

	// 파일의 용량을 구한다.
	//function get_filesize($file)
	function get_filesize($size){
		//$size = @filesize(addslashes($file));
		if ($size >= 1048576) {
			$size = number_format($size/1048576, 1) . "M";
		} else if ($size >= 1024) {
			$size = number_format($size/1024, 1) . "K";
		} else {
			$size = number_format($size, 0) . "byte";
		}
		return $size;
	}

	function thumbnail($filename, $source_path, $target_path, $thumb_width, $thumb_height, $is_create, $is_crop=false, $crop_mode='center', $is_sharpen=false, $um_value='80/0.5/3')
{
    global $g5;

    if(!$thumb_width && !$thumb_height)
        return;

    $source_file = "$source_path/$filename";

    if(!is_file($source_file)) // 원본 파일이 없다면
        return;

    $size = @getimagesize($source_file);
    if($size[2] < 1 || $size[2] > 3) // gif, jpg, png 에 대해서만 적용
        return;

    if (!is_dir($target_path)) {
        @mkdir($target_path, 0707,true);
        @chmod($target_path, 0707);
    }

    // 디렉토리가 존재하지 않거나 쓰기 권한이 없으면 썸네일 생성하지 않음
    if(!(is_dir($target_path) && is_writable($target_path)))
        return '';

    // Animated GIF는 썸네일 생성하지 않음
    if($size[2] == 1) {
        if(is_animated_gif($source_file))
            return basename($source_file);
    }

    $ext = array(1 => 'gif', 2 => 'jpg', 3 => 'png');

    $thumb_filename = preg_replace("/\.[^\.]+$/i", "", $filename); // 확장자제거
    $thumb_file = "$target_path/{$thumb_filename}_{$thumb_width}x{$thumb_height}.".$ext[$size[2]];

    $thumb_time = @filemtime($thumb_file);
    $source_time = @filemtime($source_file);

    if (file_exists($thumb_file)) {
        if ($is_create == false && $source_time < $thumb_time) {
            return basename($thumb_file);
        }
    }

    // 원본파일의 GD 이미지 생성
    $src = null;
    $degree = 0;

    if ($size[2] == 1) {
        $src = imagecreatefromgif($source_file);
        $src_transparency = imagecolortransparent($src);
    } else if ($size[2] == 2) {
        $src = imagecreatefromjpeg($source_file);

        if(function_exists('exif_read_data')) {
            // exif 정보를 기준으로 회전각도 구함
            $exif = @exif_read_data($source_file);
            if(!empty($exif['Orientation'])) {
                switch($exif['Orientation']) {
                    case 8:
                        $degree = 90;
                        break;
                    case 3:
                        $degree = 180;
                        break;
                    case 6:
                        $degree = -90;
                        break;
                }

                // 회전각도 있으면 이미지 회전
                if($degree) {
                    $src = imagerotate($src, $degree, 0);

                    // 세로사진의 경우 가로, 세로 값 바꿈
                    if($degree == 90 || $degree == -90) {
                        $tmp = $size;
                        $size[0] = $tmp[1];
                        $size[1] = $tmp[0];
                    }
                }
            }
        }
    } else if ($size[2] == 3) {
        $src = imagecreatefrompng($source_file);
        imagealphablending($src, true);
    } else {
        return;
    }

    if(!$src)
        return;

    $is_large = true;
    // width, height 설정
    if($thumb_width) {
        if(!$thumb_height) {
            $thumb_height = round(($thumb_width * $size[1]) / $size[0]);
        } else {
            if($size[0] < $thumb_width || $size[1] < $thumb_height)
                $is_large = false;
        }
    } else {
        if($thumb_height) {
            $thumb_width = round(($thumb_height * $size[0]) / $size[1]);
        }
    }

    $dst_x = 0;
    $dst_y = 0;
    $src_x = 0;
    $src_y = 0;
    $dst_w = $thumb_width;
    $dst_h = $thumb_height;
    $src_w = $size[0];
    $src_h = $size[1];

    $ratio = $dst_h / $dst_w;

    if($is_large) {
        // 크롭처리
        if($is_crop) {
            switch($crop_mode)
            {
                case 'center':
                    if($size[1] / $size[0] >= $ratio) {
                        $src_h = round($src_w * $ratio);
                        $src_y = round(($size[1] - $src_h) / 2);
                    } else {
                        $src_w = round($size[1] / $ratio);
                        $src_x = round(($size[0] - $src_w) / 2);
                    }
                    break;
                default:
                    if($size[1] / $size[0] >= $ratio) {
                        $src_h = round($src_w * $ratio);
                    } else {
                        $src_w = round($size[1] / $ratio);
                    }
                    break;
            }
        }

        $dst = imagecreatetruecolor($dst_w, $dst_h);

        if($size[2] == 3) {
            imagealphablending($dst, false);
            imagesavealpha($dst, true);
        } else if($size[2] == 1) {
            $palletsize = imagecolorstotal($src);
            if($src_transparency >= 0 && $src_transparency < $palletsize) {
                $transparent_color   = imagecolorsforindex($src, $src_transparency);
                $current_transparent = imagecolorallocate($dst, $transparent_color['red'], $transparent_color['green'], $transparent_color['blue']);
                imagefill($dst, 0, 0, $current_transparent);
                imagecolortransparent($dst, $current_transparent);
            }
        }
    } else {
        $dst = imagecreatetruecolor($dst_w, $dst_h);
        $bgcolor = imagecolorallocate($dst, 255, 255, 255); // 배경색

        if($src_w < $dst_w) {
            if($src_h >= $dst_h) {
                $dst_x = round(($dst_w - $src_w) / 2);
                $src_h = $dst_h;
            } else {
                $dst_x = round(($dst_w - $src_w) / 2);
                $dst_y = round(($dst_h - $src_h) / 2);
                $dst_w = $src_w;
                $dst_h = $src_h;
            }
        } else {
            if($src_h < $dst_h) {
                $dst_y = round(($dst_h - $src_h) / 2);
                $dst_h = $src_h;
                $src_w = $dst_w;
            }
        }

        if($size[2] == 3) {
            $bgcolor = imagecolorallocatealpha($dst, 0, 0, 0, 127);
            imagefill($dst, 0, 0, $bgcolor);
            imagealphablending($dst, false);
            imagesavealpha($dst, true);
        } else if($size[2] == 1) {
            $palletsize = imagecolorstotal($src);
            if($src_transparency >= 0 && $src_transparency < $palletsize) {
                $transparent_color   = imagecolorsforindex($src, $src_transparency);
                $current_transparent = imagecolorallocate($dst, $transparent_color['red'], $transparent_color['green'], $transparent_color['blue']);
                imagefill($dst, 0, 0, $current_transparent);
                imagecolortransparent($dst, $current_transparent);
            } else {
                imagefill($dst, 0, 0, $bgcolor);
            }
        } else {
            imagefill($dst, 0, 0, $bgcolor);
        }
    }

    imagecopyresampled($dst, $src, $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h);

    // sharpen 적용
    if($is_sharpen && $is_large) {
        $val = explode('/', $um_value);
        UnsharpMask($dst, $val[0], $val[1], $val[2]);
    }

    if($size[2] == 1) {
        imagegif($dst, $thumb_file);
    } else if($size[2] == 3) {
        
          $png_compress = 5;
        
        imagepng($dst, $thumb_file, $png_compress);
    } else {
            $jpg_quality = 90;
        
        imagejpeg($dst, $thumb_file, $jpg_quality);
    }

    chmod($thumb_file, 0606); // 추후 삭제를 위하여 파일모드 변경

    imagedestroy($src);
    imagedestroy($dst);

    return basename($thumb_file);
}

function UnsharpMask($img, $amount, $radius, $threshold)    {

/*
출처 : http://vikjavev.no/computing/ump.php
New:
- In version 2.1 (February 26 2007) Tom Bishop has done some important speed enhancements.
- From version 2 (July 17 2006) the script uses the imageconvolution function in PHP
version >= 5.1, which improves the performance considerably.


Unsharp masking is a traditional darkroom technique that has proven very suitable for
digital imaging. The principle of unsharp masking is to create a blurred copy of the image
and compare it to the underlying original. The difference in colour values
between the two images is greatest for the pixels near sharp edges. When this
difference is subtracted from the original image, the edges will be
accentuated.

The Amount parameter simply says how much of the effect you want. 100 is 'normal'.
Radius is the radius of the blurring circle of the mask. 'Threshold' is the least
difference in colour values that is allowed between the original and the mask. In practice
this means that low-contrast areas of the picture are left unrendered whereas edges
are treated normally. This is good for pictures of e.g. skin or blue skies.

Any suggenstions for improvement of the algorithm, expecially regarding the speed
and the roundoff errors in the Gaussian blur process, are welcome.

*/

////////////////////////////////////////////////////////////////////////////////////////////////
////
////                  Unsharp Mask for PHP - version 2.1.1
////
////    Unsharp mask algorithm by Torstein Hønsi 2003-07.
////             thoensi_at_netcom_dot_no.
////               Please leave this notice.
////
///////////////////////////////////////////////////////////////////////////////////////////////



    // $img is an image that is already created within php using
    // imgcreatetruecolor. No url! $img must be a truecolor image.

    // Attempt to calibrate the parameters to Photoshop:
    if ($amount > 500)    $amount = 500;
    $amount = $amount * 0.016;
    if ($radius > 50)    $radius = 50;
    $radius = $radius * 2;
    if ($threshold > 255)    $threshold = 255;

    $radius = abs(round($radius));     // Only integers make sense.
    if ($radius == 0) {
        return $img; imagedestroy($img);        }
    $w = imagesx($img); $h = imagesy($img);
    $imgCanvas = imagecreatetruecolor($w, $h);
    $imgBlur = imagecreatetruecolor($w, $h);


    // Gaussian blur matrix:
    //
    //    1    2    1
    //    2    4    2
    //    1    2    1
    //
    //////////////////////////////////////////////////


    if (function_exists('imageconvolution')) { // PHP >= 5.1
            $matrix = array(
            array( 1, 2, 1 ),
            array( 2, 4, 2 ),
            array( 1, 2, 1 )
        );
        $divisor = array_sum(array_map('array_sum', $matrix));
        $offset = 0;

        imagecopy ($imgBlur, $img, 0, 0, 0, 0, $w, $h);
        imageconvolution($imgBlur, $matrix, $divisor, $offset);
    }
    else {

    // Move copies of the image around one pixel at the time and merge them with weight
    // according to the matrix. The same matrix is simply repeated for higher radii.
        for ($i = 0; $i < $radius; $i++)    {
            imagecopy ($imgBlur, $img, 0, 0, 1, 0, $w - 1, $h); // left
            imagecopymerge ($imgBlur, $img, 1, 0, 0, 0, $w, $h, 50); // right
            imagecopymerge ($imgBlur, $img, 0, 0, 0, 0, $w, $h, 50); // center
            imagecopy ($imgCanvas, $imgBlur, 0, 0, 0, 0, $w, $h);

            imagecopymerge ($imgBlur, $imgCanvas, 0, 0, 0, 1, $w, $h - 1, 33.33333 ); // up
            imagecopymerge ($imgBlur, $imgCanvas, 0, 1, 0, 0, $w, $h, 25); // down
        }
    }

    if($threshold>0){
        // Calculate the difference between the blurred pixels and the original
        // and set the pixels
        for ($x = 0; $x < $w-1; $x++)    { // each row
            for ($y = 0; $y < $h; $y++)    { // each pixel

                $rgbOrig = ImageColorAt($img, $x, $y);
                $rOrig = (($rgbOrig >> 16) & 0xFF);
                $gOrig = (($rgbOrig >> 8) & 0xFF);
                $bOrig = ($rgbOrig & 0xFF);

                $rgbBlur = ImageColorAt($imgBlur, $x, $y);

                $rBlur = (($rgbBlur >> 16) & 0xFF);
                $gBlur = (($rgbBlur >> 8) & 0xFF);
                $bBlur = ($rgbBlur & 0xFF);

                // When the masked pixels differ less from the original
                // than the threshold specifies, they are set to their original value.
                $rNew = (abs($rOrig - $rBlur) >= $threshold)
                    ? max(0, min(255, ($amount * ($rOrig - $rBlur)) + $rOrig))
                    : $rOrig;
                $gNew = (abs($gOrig - $gBlur) >= $threshold)
                    ? max(0, min(255, ($amount * ($gOrig - $gBlur)) + $gOrig))
                    : $gOrig;
                $bNew = (abs($bOrig - $bBlur) >= $threshold)
                    ? max(0, min(255, ($amount * ($bOrig - $bBlur)) + $bOrig))
                    : $bOrig;



                if (($rOrig != $rNew) || ($gOrig != $gNew) || ($bOrig != $bNew)) {
                        $pixCol = ImageColorAllocate($img, $rNew, $gNew, $bNew);
                        ImageSetPixel($img, $x, $y, $pixCol);
                    }
            }
        }
    }
    else{
        for ($x = 0; $x < $w; $x++)    { // each row
            for ($y = 0; $y < $h; $y++)    { // each pixel
                $rgbOrig = ImageColorAt($img, $x, $y);
                $rOrig = (($rgbOrig >> 16) & 0xFF);
                $gOrig = (($rgbOrig >> 8) & 0xFF);
                $bOrig = ($rgbOrig & 0xFF);

                $rgbBlur = ImageColorAt($imgBlur, $x, $y);

                $rBlur = (($rgbBlur >> 16) & 0xFF);
                $gBlur = (($rgbBlur >> 8) & 0xFF);
                $bBlur = ($rgbBlur & 0xFF);

                $rNew = ($amount * ($rOrig - $rBlur)) + $rOrig;
                    if($rNew>255){$rNew=255;}
                    elseif($rNew<0){$rNew=0;}
                $gNew = ($amount * ($gOrig - $gBlur)) + $gOrig;
                    if($gNew>255){$gNew=255;}
                    elseif($gNew<0){$gNew=0;}
                $bNew = ($amount * ($bOrig - $bBlur)) + $bOrig;
                    if($bNew>255){$bNew=255;}
                    elseif($bNew<0){$bNew=0;}
                $rgbNew = ($rNew << 16) + ($gNew <<8) + $bNew;
                    ImageSetPixel($img, $x, $y, $rgbNew);
            }
        }
    }
    imagedestroy($imgCanvas);
    imagedestroy($imgBlur);

    return true;

}

function is_animated_gif($filename) {
    if(!($fh = @fopen($filename, 'rb')))
        return false;
    $count = 0;
    // 출처 : http://www.php.net/manual/en/function.imagecreatefromgif.php#104473
    // an animated gif contains multiple "frames", with each frame having a
    // header made up of:
    // * a static 4-byte sequence (\x00\x21\xF9\x04)
    // * 4 variable bytes
    // * a static 2-byte sequence (\x00\x2C) (some variants may use \x00\x21 ?)

    // We read through the file til we reach the end of the file, or we've found
    // at least 2 frame headers
    while(!feof($fh) && $count < 2) {
        $chunk = fread($fh, 1024 * 100); //read 100kb at a time
        $count += preg_match_all('#\x00\x21\xF9\x04.{4}\x00(\x2C|\x21)#s', $chunk, $matches);
   }

    fclose($fh);
    return $count > 1;
}


// 게시판 첨부파일 썸네일 삭제
function delete_board_thumbnail($table, $file)
{
    if(!$table || !$file)
        return;

    $fn = preg_replace("/\.[^\.]+$/i", "", basename($file));
    $files = glob(_BB_DATA_PATH.'/file/'.$table.'/thumb_'.$fn.'*');
    if (is_array($files)) {
        foreach ($files as $filename)
            unlink($filename);
    }
}

function js_str($s){
    return '"' . addcslashes($s, "\0..\37\"\\") . '"';
}

function js_array($array){
    $temp = array_map('js_str', $array);
    return '[' . implode(',', $temp) . ']';
}
	




	
	function httpGet($url , $release, $fields=""){

		if($release){

			$token = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJodHRwOi8vd3d3LnczLm9yZy8yMDAxLzA0L3htbGRzaWctbW9yZSNobWFjLXNoYTM4NCJ9.eyJpc3MiOiJodHRwOi8vb3BlbmFwaS5oc2l0LmNvLmtyIiwiYXVkIjoiMDk5MTUzYzI2MjUxNDliYzhlY2IzZTg1ZTAzZjAwMjIiLCJleHAiOjE2OTg1NjUxMTYsIm5iZiI6MTYwMzk1NzExNn0.Zo9NH6r8odqQskS2P7V-IuuRqonFl_nL4Bjx4lQsrRNy_J9g3gBIw1vgdUEyUSeg';
		} else {
			$token = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJodHRwOi8vd3d3LnczLm9yZy8yMDAxLzA0L3htbGRzaWctbW9yZSNobWFjLXNoYTM4NCJ9.eyJpc3MiOiJodHRwOi8vb3BlbmFwaS5oc2l0LmNvLmtyIiwiYXVkIjoiMDk5MTUzYzI2MjUxNDliYzhlY2IzZTg1ZTAzZjAwMjIiLCJleHAiOjE2OTg1NjUwNjgsIm5iZiI6MTYwMzk1NzA2OH0.vR7ygzLWDP38NTDcJSqAgy2JOO5nTukE5yAlD5OrIFtcub1wLmFPrszcClBhUuFS';
		}
		
		
		$ch = curl_init();

		$headers = array(
			'Content-Type: application/json',
			sprintf('Authorization: Bearer %1$s', $token)
		);

		curl_setopt($ch,CURLOPT_URL,$url);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch,CURLOPT_SSL_VERIFYPEER, false);
		if( $fields !=""){
			curl_setopt( $ch,CURLOPT_POSTFIELDS, json_encode( $fields ) );
		}
		$output=curl_exec($ch);
		curl_close($ch);
		return $output;
	}

	function userInfo($MB_CODE,$DB){
		global $_BB_DB_TABLE;
		$where = " WHERE MB_CODE = '".$MB_CODE."' ";
		$sql = " SELECT * FROM ".$_BB_DB_TABLE['MEMBER'].$where;
		$userInfo = rf_mysql_row($sql, $DB);
		
		
		return array(
			'uUserCode' => $userInfo['MB_CODE'], 
			'uUserId' => $userInfo['MB_ID'],
			'uUserPassword' =>$userInfo['MB_PASSWORD'],
			'uUserUse' => $userInfo['MB_USE'],
			'uUserGroupCode' => $userInfo['G_CODE'],
			'uUserMemo' => $userInfo['MB_MEMO'],
		);
	}

	function storeInfo($ST_CODE,$DB){
		global $_BB_DB_TABLE;
		$where = " WHERE ST_CODE = '".$ST_CODE."' ";
		$sql = " SELECT * FROM ".$_BB_DB_TABLE['STORE'].$where;
		$storeInfo = rf_mysql_row($sql, $DB);
		
		
		return array(
			'storeCode' => $storeInfo['ST_CODE'], 
			'storeName' => $storeInfo['ST_NAME'],
			'storeMemo' =>$storeInfo['ST_MEMO'],
			'storeProductList' => $storeInfo['PD_MODAL_LIST'],
			'storeUse' => $storeInfo['ST_USE'],
		);
	}



	class _BRIGHTBELLPASS
	{
		var $key = '_BRIGHTBELL14VED_';
		var $iv = 'BRIGHTBELL[*&^%]';
	 
		function encryptToken($data)
		{
			// Mcrypt library has been DEPRECATED since PHP 7.1, use openssl:
			return openssl_encrypt($data, 'aes-256-cbc', $this->key, OPENSSL_RAW_DATA, $this->iv);
			//$padding = 16 - (strlen($data) % 16);
			//$data .= str_repeat(chr($padding), $padding);
			//return mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $this->key, $data, MCRYPT_MODE_CBC, $this->iv);
		}
	 
		function decryptToken($data)
		{
			// Mcrypt library has been DEPRECATED since PHP 7.1, use openssl:
			 return openssl_decrypt(base64_decode($data), 'aes-256-cbc', $this->key, OPENSSL_RAW_DATA, $this->iv);
			//$data = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $this->key, base64_decode($data), MCRYPT_MODE_CBC, $this->iv);
			//$padding = ord($data[strlen($data) - 1]);
			//return substr($data, 0, -$padding);
		}
	}



	 function GenerateString($length)  
	{  
		$characters  = "0123456789";  
		$characters .= "abcdefghijklmnopqrstuvwxyz";  
		$characters .= "ABCDEFGHIJKLMNOPQRSTUVWXYZ";  
		$characters .= "_";  
		  
		$string_generated = "";  
		  
		$nmr_loops = $length;  
		while ($nmr_loops--)  
		{  
			$string_generated .= $characters[mt_rand(0, strlen($characters) - 1)];  
		}  
		  
		return $string_generated;  
	}  


	function validateURL($URL) {
      $pattern_1 = "/^(http|https|ftp):\/\/(([A-Z0-9][A-Z0-9_-]*)(\.[A-Z0-9][A-Z0-9_-]*)+.(com|org|net|dk|at|us|tv|info|uk|co.uk|biz|se)$)(:(\d+))?\/?/i";
      $pattern_2 = "/^(www)((\.[A-Z0-9][A-Z0-9_-]*)+.(com|org|net|dk|at|us|tv|info|uk|co.uk|biz|se)$)(:(\d+))?\/?/i";       
      if(preg_match($pattern_1, $URL) || preg_match($pattern_2, $URL)){
        return true;
      } else{
        return false;
      }
    }

	function getBrowserInfo(){
		$userAgent = $_SERVER["HTTP_USER_AGENT"]; 
		if(preg_match('/MSIE/i',$userAgent) && !preg_match('/Opera/i',$u_agent)){
			$browser = 'Internet Explorer';
		}
		else if(preg_match('/Firefox/i',$userAgent)){
			$browser = 'Mozilla Firefox';
		}
		else if (preg_match('/Chrome/i',$userAgent)){
			$browser = 'Google Chrome';
		}
		else if(preg_match('/Safari/i',$userAgent)){
			$browser = 'Apple Safari';
		}
		elseif(preg_match('/Opera/i',$userAgent)){
			$browser = 'Opera';
		}
		elseif(preg_match('/Netscape/i',$userAgent)){
			$browser = 'Netscape';
		}
		else{
			$browser = "Other";
		}

		return $browser;
	}

	function getOsInfo(){
		$userAgent = $_SERVER["HTTP_USER_AGENT"]; 

		if (preg_match('/linux/i', $userAgent)){ 
			$os = 'linux';}
		elseif(preg_match('/macintosh|mac os x/i', $userAgent)){
			$os = 'mac';}
		elseif (preg_match('/windows|win32/i', $userAgent)){
			$os = 'windows';}
		elseif (preg_match('/Android/i', $userAgent)){
			$os = 'Android';}
		elseif (preg_match('/iPhone|iPod/i', $userAgent)){
			$os = 'iOS';}
		elseif (preg_match('/Blackberry/i', $userAgent)){
			$os = 'Blackberry';}
		else {
			$os = 'Other';
		}
		return $os;
	}

	function getMobileInfo(){
		$detect = new Mobile_Detect; //예제소스를 호출하는 웹브라우져가 모바일인지 아닌지를 체크함(1:모바일, 2:PC 등) 
		$devicetype = $detect->isMobile() ? 1 : 2; //모바일일때만 처리 
		if($devicetype == 1){ 
			$devicemake = ""; //제조사 변수 초기화 
			foreach($detect->getRules() as $name => $regex){ 
				if(substr($name, -2) == "OS") break; //제조사 정보만을 위해 OS정보는 제외처리(데모참조) 
				$check = $detect->{'is'.$name}(); 
				if($check) { 
					$devicemake = str_replace("Tablet","",$name); //Tablet정보도 있음(데모참조) 
					break; 
				} 
			} //디바이스OS 체크함(iOS, Android, ETC-WindowsMobile등) 
			
			$deviceos = $detect->isiOS() ? "iOS" : ($detect->isAndroidOS() ? "Android" : "ETC"); 
			if($deviceos == "iOS"){ 
				$deviceosv = $detect->version("iOS"); //디바이스 OS 버전 정보(iOS) 
			} else if ($deviceos == "Android"){ 
				$deviceosv = $detect->version("Android"); //디바이스 OS 버전 정보(Android) 
			} else { 
				$deviceosv = $detect->version("Mobile"); //디바이스 OS 버전 정보(ETC) 
			}
			
			return $deviceos;
		}
		return "";
	}

	function checkGeoip(){
		$ip = $_SERVER['REMOTE_ADDR']; // 아이피 주소받음
		$details = json_decode(file_get_contents("http://ipinfo.io/"));// 받음받음
		//echo $details->country;
		//echo $details->city;
		return $details->country;
	}

	function get_client_ip() {
		$ipaddress = '';
		if (getenv('HTTP_CLIENT_IP')){
			$ipaddress = getenv('HTTP_CLIENT_IP');
		} else if(getenv('HTTP_X_FORWARDED_FOR')){
			$ipaddress = getenv('HTTP_X_FORWARDED_FOR');
		} else if(getenv('HTTP_X_FORWARDED'))
			$ipaddress = getenv('HTTP_X_FORWARDED');
		else if(getenv('HTTP_FORWARDED_FOR'))
			$ipaddress = getenv('HTTP_FORWARDED_FOR');
		else if(getenv('HTTP_FORWARDED'))
			$ipaddress = getenv('HTTP_FORWARDED');
		else if(getenv('REMOTE_ADDR'))
			$ipaddress = getenv('REMOTE_ADDR');
		else
			$ipaddress = 'UNKNOWN';
		return $ipaddress;
	}  


?>