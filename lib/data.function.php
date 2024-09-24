<?php
	
	function makeCode($DB,$table,$column,$key) { 
		$s = strtoupper(md5(uniqid("_HDSCXBB_",true))); 
		$guidText = 
			$key.
			substr($s,0,2).
			substr($s,8,2).
			substr($s,12,4).
			substr($s,16,4).
			substr($s,2);
		$UserWhere = " WHERE ".$column." = '".$guidText."' ";
		$userCodesql = " SELECT COUNT(*) AS CNT FROM ".$table." ".$UserWhere;
		$userCode = rf_mysql_row($userCodesql, $DB);
		
		if($userCode['CNT'] != 0){
			makeCode($DB,$table,$column,$key);
			return;
		}
		return $guidText;
	}
	function certCode($DB,$table,$column) { 
		$s = strtoupper(md5(uniqid("_HDSCXBB_",true))); 
		$guidText = 
			'CERT'.
			substr($s,0,2).
			substr($s,8,2).
			substr($s,12,4).
			substr($s,16,4).
			substr($s,2);
		$UserWhere = " WHERE ".$column." = '".$guidText."' ";
		$userCodesql = " SELECT COUNT(*) AS CNT FROM ".$table." ".$UserWhere;
		$userCode = rf_mysql_row($userCodesql, $DB);
		
		if($userCode['CNT'] != 0){
			certCode($DB);
			return;
		}
		return $guidText;
	}

	function useruid($DB,$table,$column) { 
		$s = strtoupper(md5(uniqid("_HDSCXBB_",true))); 
		$guidText = 
			''.
			substr($s,0,2).
			substr($s,8,2).
			//substr($s,12,4).
			//substr($s,16,4).
			substr($s,2);
		$UserWhere = " WHERE ".$column." = '".$guidText."' ";
		$userCodesql = " SELECT COUNT(*) AS CNT FROM ".$table." ".$UserWhere;
		$userCode = rf_mysql_row($userCodesql, $DB);
		
		if($userCode['CNT'] != 0){
			useruid($DB);
			return;
		}
		return $guidText;
	}

	function userpetuid($DB,$table,$column)	{
	   $s = strtoupper(md5(uniqid("_HDSCXBB_",true))); 
		$guidText = 
			'PET'.
			substr($s,0,2).
			substr($s,8,2).
			//substr($s,12,4).
			//substr($s,16,4).
			substr($s,2);
		$UserWhere = " WHERE ".$column." = '".$guidText."' ";
		$userCodesql = " SELECT COUNT(*) AS CNT FROM ".$table." ".$UserWhere;
		$userCode = rf_mysql_row($userCodesql, $DB);
		
		if($userCode['CNT'] != 0){
			userpetuid($DB);
			return;
		}
		return $guidText;
	}

	function fileuid($DB,$table,$column)	{
	   $s = strtoupper(md5(uniqid("_HDSCXBB_",true))); 
		$guidText = 
			'FILE'.
			substr($s,0,2).
			substr($s,8,2).
			//substr($s,12,4).
			//substr($s,16,4).
			substr($s,2);
		$UserWhere = " WHERE ".$column." = '".$guidText."' ";
		$userCodesql = " SELECT COUNT(*) AS CNT FROM ".$table." ".$UserWhere;
		$userCode = rf_mysql_row($userCodesql, $DB);
		
		if($userCode['CNT'] != 0){
			fileuid($DB);
			return;
		}
		return $guidText;
	}

	


	function admuid($DB,$table,$column) { 
		$s = strtoupper(md5(uniqid("_HDSCXBB_",true))); 
		$guidText = 
			'ADM'.
			substr($s,0,2).
			substr($s,8,2).
			//substr($s,12,4).
			//substr($s,16,4).
			substr($s,2);
		$UserWhere = " WHERE ".$column." = '".$guidText."' ";
		$userCodesql = " SELECT COUNT(*) AS CNT FROM ".$table." ".$UserWhere;
		$userCode = rf_mysql_row($userCodesql, $DB);
		
		if($userCode['CNT'] != 0){
			admuid($DB);
			return;
		}
		return $guidText;
	}

	function uid($DB,$table,$column) { 
		$s = strtoupper(md5(uniqid("_HDSCXBB_",true))); 
		$guidText = 
			'I'.
			substr($s,0,8).
			substr($s,8,4).
			//substr($s,12,4).
			//substr($s,16,4).
			substr($s,2,2);
		$UserWhere = " WHERE ".$column." = '".$guidText."' ";
		$userCodesql = " SELECT COUNT(*) AS CNT FROM ".$table." ".$UserWhere;
		$userCode = rf_mysql_row($userCodesql, $DB);
		
		if($userCode['CNT'] != 0){
			uid($DB);
			return;
		}
		return $guidText;
	}

	function accountuid($DB,$table,$column) { 
		$s = strtoupper(md5(uniqid("_HDSCXBB_",true))); 
		$guidText = 
			'ACC'.
			substr($s,0,8).
			substr($s,8,4).
			//substr($s,12,4).
			//substr($s,16,4).
			substr($s,10);
		$UserWhere = " WHERE ".$column." = '".$guidText."' ";
		$userCodesql = " SELECT COUNT(*) AS CNT FROM ".$table." ".$UserWhere;
		$userCode = rf_mysql_row($userCodesql, $DB);
		
		if($userCode['CNT'] != 0){
			accountuid($DB,$table,$column);
			return;
		}
		return $guidText;
	}

	function saveLog($type,$log_type,$log_title,$log_detail,$log_before,$log_after,$log_user_code,$log_reg_user_code){
		global $DB,$_HDSCXBB_DB_TABLE,$BB_DATE,$root_f;

		if($type =='USER_LOG'){
		 	$insertLogSql = " INSERT INTO ".$_HDSCXBB_DB_TABLE['USER_LOG']." SET 
				LOG_TYPE = '".ESC($log_type,$DB)."',
				LOG_TITLE = '".ESC($log_title,$DB)."',
				LOG_DETAIL = '".ESC($log_detail,$DB)."',
				LOG_BEFORE = '".ESC($log_before,$DB)."',
				LOG_AFTER = '".ESC($log_after,$DB)."',
				USER_CODE = '".ESC($log_user_code,$DB)."',
				REG_DATE = '".$BB_DATE."',
				REG_USER_CODE = '".ESC($log_reg_user_code,$DB)."'			
			";
			rf_mysql_query($insertLogSql, $DB);
		}


	}


	function user($user_code){
		global $DB,$_HDSCXBB_DB_TABLE;
		$userSql = "SELECT * FROM ".$_HDSCXBB_DB_TABLE['USER']." WHERE USER_CODE = '".$user_code."' ";
		$user = rf_mysql_row($userSql, $DB);

		return 	 $user;
	}

	function fileUploadContentEach($file,$targetCode,$targetTable,$bf_no = 0){
		global $DB,$_HDSCXBB_DB_TABLE,$BB_DATE;
		$fileUidArray = [];
		$count = 0;
		$targetPathName = "/".$targetTable."/";
		$targetPath = _BB_APP_DATA_PATH . $targetPathName;
		@mkdir($targetPath, 0707,true);
		@chmod($targetPath, 0707);
		$chars_array = array_merge(range(0,9), range('a','z'), range('A','Z')); 
		if ( !empty($file) ) {
			if ( is_dir($targetPath) ) {
				if ( is_writable($targetPath) ) {  
					//echo  count($file['name']);
					
						$tempFile = $file['tmp_name'];
						$filesize  = $file['size'];
						$filename  = $file['name'];

						$filename  = preg_replace('/(\s|\<|\>|\=|\(|\))/', '_', $filename);
						
						$timg = @getimagesize($tempFile);
						// image type
						if ( preg_match("/\.(gif|jpg|jpeg|png|GIF|JPG|JPEG|PNG|MP4|mp4|MP3|mp3|AVI|avi)$/i", $filename) ) 
						{
//							if ($timg[2] < 1 || $timg[2] > 16)
//							{
//								//$file_upload_msg .= "\'{$filename}\' 파일이 이미지나 플래시 파일이 아닙니다.\\n";
//							}
						}
						$upload['image'] = $timg;
						$upload['source'] = $filename;
						$upload['filesize'] = $filesize;
						$upload['mime_type'] = get_mime_type($filename);
						$upload['mime'] = get_mime($filename);
						

						$filename = preg_replace("/\.(php|phtm|htm|cgi|pl|exe|jsp|asp|inc)/i", "$0-x", $filename);

						shuffle($chars_array);
						$shuffle = implode("", $chars_array);

						$upload['file'] = $userCode.'_'.abs(ip2long($_SERVER['REMOTE_ADDR'])).'_'.substr($shuffle,0,8).'_'.str_replace('%', '', urlencode(str_replace(' ', '_', $filename))); 
						debug($upload['file']);
						$dest_file = $targetPath . $upload['file'];
						$targetFile = $targetPath . $file['name'];   
						
						if ( !file_exists($dest_file) ) {
							// Upload the file
							
							move_uploaded_file($tempFile, $dest_file);
							$thumbnail_info = '';
//							if( strpos($upload['mime_type'],'image')==0){
//								$thumbnail_dest_file = $targetPath."thumb_nail/thumb_" . $upload['file'];
//								$thumbnail_info =  thumbnail($upload['file'], $targetPath, $targetPath."thumb_nail/", 300, 300, false,true);
//								if($thumbnail_info !=""){
//									$thumbnail_info = "thumb_nail/".$thumbnail_info;
//								}
//							}
							 debug($upload);
							// Be sure that the file has been uploaded
							if ( file_exists($dest_file) ) {
									
								
								chmod($dest_file, 0606);
								
								$file_uid = fileuid($DB,$_HDSCXBB_DB_TABLE['CONTENT_FILES'],'BF_CODE');
								$sql = " insert into ".$_HDSCXBB_DB_TABLE['CONTENT_FILES']."
										set 
											BF_CODE = '".$file_uid."',
											BF_NO = '".$bf_no."',
											BF_TABLE = '".$targetTable."',
											BF_TARGET_CODE = '".$targetCode."',
											BF_SOURCE = '".$upload['source']."',
											BF_FILE = '".$upload['file']."',
											BF_DOWNLOAD = 0,
											BF_CONTENT = '',
											BF_THUMBURL = '".$thumbnail_info."',
											BF_FILESIZE = '".$upload['filesize']."', 
											 ";
										if($upload['image'] != ""){
											$sql .= "	BF_WIDTH = '".$upload['image'][0]."',
														BF_HEIGHT = '".$upload['image'][1]."',
														BF_TYPE = '".$upload['image'][2]."', ";
										}
										if($fileSelectType != ""){
											$sql .= "	BF_SELECT_TYPE = '".$fileSelectType."', ";
										}
									
											$sql .= "			
											BF_MIME_TYPE = '".$upload['mime_type']."',
											BF_MIME = '".$upload['mime']."',
											BF_DATETIME = '".$BB_DATE."' ";
								 debug($sql);
								 rf_mysql_query($sql, $DB);
								
								$fileUidArray =  $file_uid;

							}
						}

					}

				
			}

		}

		return $fileUidArray;
	}

	function fileUploadContent($file,$targetCode,$targetTable,$bf_no = 0){
		global $DB,$_HDSCXBB_DB_TABLE,$BB_DATE;
		$fileUidArray = [];
		$count = 0;
		$targetPathName = "/".$targetTable."/";
		$targetPath = _BB_APP_DATA_PATH . $targetPathName;
		@mkdir($targetPath, 0707,true);
		@chmod($targetPath, 0707);
		$chars_array = array_merge(range(0,9), range('a','z'), range('A','Z')); 
		if ( !empty($file) ) {
			if ( is_dir($targetPath) ) {
				if ( is_writable($targetPath) ) {  
					//echo  count($file['name']);
					for($i=0;$i<count($file['name']);$i++){ 
						$tempFile = $file['tmp_name'][$i];
						$filesize  = $file['size'][$i];
						$filename  = $file['name'][$i];
						$fileSelectType = $filesJson[$i]['selectType'];
						$filename  = preg_replace('/(\s|\<|\>|\=|\(|\))/', '_', $filename);
						
						$timg = @getimagesize($tempFile);
						// image type
						if ( preg_match("/\.(gif|jpg|jpeg|png|GIF|JPG|JPEG|PNG|MP4|mp4|MP3|mp3|AVI|avi)$/i", $filename) ) 
						{
//							if ($timg[2] < 1 || $timg[2] > 16)
//							{
//								//$file_upload_msg .= "\'{$filename}\' 파일이 이미지나 플래시 파일이 아닙니다.\\n";
//							}
						}
						$upload['image'] = $timg;
						$upload['source'] = $filename;
						$upload['filesize'] = $filesize;
						$upload['mime_type'] = get_mime_type($filename);
						$upload['mime'] = get_mime($filename);
						

						$filename = preg_replace("/\.(php|phtm|htm|cgi|pl|exe|jsp|asp|inc)/i", "$0-x", $filename);

						shuffle($chars_array);
						$shuffle = implode("", $chars_array);

						$upload['file'] = $userCode.'_'.abs(ip2long($_SERVER['REMOTE_ADDR'])).'_'.substr($shuffle,0,8).'_'.str_replace('%', '', urlencode(str_replace(' ', '_', $filename))); 

						$dest_file = $targetPath . $upload['file'];
						$targetFile = $targetPath . $file['name'][$i];   
						if ( !file_exists($targetFile) ) {
							// Upload the file
							move_uploaded_file($tempFile, $dest_file);
							$thumbnail_info = '';
//							if( strpos($upload['mime_type'],'image')==0){
//								$thumbnail_dest_file = $targetPath."thumb_nail/thumb_" . $upload['file'];
//								$thumbnail_info =  thumbnail($upload['file'], $targetPath, $targetPath."thumb_nail/", 300, 300, false,true);
//								if($thumbnail_info !=""){
//									$thumbnail_info = "thumb_nail/".$thumbnail_info;
//								}
//							}
							
							// Be sure that the file has been uploaded
							if ( file_exists($dest_file) ) {
								
								
								chmod($dest_file, 0606);
								
								$file_uid = fileuid($DB,$_HDSCXBB_DB_TABLE['CONTENT_FILES'],'BF_CODE');
								$sql = " insert into ".$_HDSCXBB_DB_TABLE['CONTENT_FILES']."
										set 
											BF_CODE = '".$file_uid."',
											BF_NO = '".$bf_no."',
											BF_TABLE = '".$targetTable."',
											BF_TARGET_CODE = '".$targetCode."',
											BF_SOURCE = '".$upload['source']."',
											BF_FILE = '".$upload['file']."',
											BF_DOWNLOAD = 0,
											BF_CONTENT = '',
											BF_THUMBURL = '".$thumbnail_info."',
											BF_FILESIZE = '".$upload['filesize']."', 
											 ";
										if($upload['image'] != ""){
											$sql .= "	BF_WIDTH = '".$upload['image'][0]."',
														BF_HEIGHT = '".$upload['image'][1]."',
														BF_TYPE = '".$upload['image'][2]."', ";
										}
										if($fileSelectType != ""){
											$sql .= "	BF_SELECT_TYPE = '".$fileSelectType."', ";
										}
									
											$sql .= "			
											BF_MIME_TYPE = '".$upload['mime_type']."',
											BF_MIME = '".$upload['mime']."',
											BF_DATETIME = '".$BB_DATE."' ";

								 rf_mysql_query($sql, $DB);
								
								$fileUidArray[] =  $file_uid;

							}
						}

					}

				}
			}

		}

		return $fileUidArray;
	}


	function uploadFileDelete($files,$targetTable){
		debug($files);
		global $DB,$_HDSCXBB_DB_TABLE,$BB_DATE;
		$targetFiles = json_decode($files,true);
		$targetPathName = "/".$targetTable."/";
		$targetPath = _BB_APP_DATA_PATH . $targetPathName;
		@mkdir($targetPath, 0707,true);
		@chmod($targetPath, 0707); 
		foreach($targetFiles as $cmt){
			$selectFileSql = " SELECT * FROM ".$_HDSCXBB_DB_TABLE['CONTENT_FILES']." WHERE BF_CODE = '".$cmt."' ";
			$selectFile = rf_mysql_row($selectFileSql, $DB);
			if($selectFile != ''){
				$dest_file = $targetPath . $selectFile['BF_FILE'];
				if ( file_exists($dest_file) ) {
					@unlink($dest_file);
					$deleteSql = " DELETE FROM ".$_HDSCXBB_DB_TABLE['CONTENT_FILES']." WHERE BF_CODE = '".$cmt."'";
					rf_mysql_query($deleteSql, $DB);
				}
			}
		}
	}

	function uploadFileDeleteEach($filesCode,$targetTable){
		global $DB,$_HDSCXBB_DB_TABLE,$BB_DATE;
		
		$targetPathName = "/".$targetTable."/";
		$targetPath = _BB_APP_DATA_PATH . $targetPathName;
		@mkdir($targetPath, 0707,true);
		@chmod($targetPath, 0707); 
		
			$selectFileSql = " SELECT * FROM ".$_HDSCXBB_DB_TABLE['CONTENT_FILES']." WHERE BF_CODE = '".$filesCode."' ";
			$selectFile = rf_mysql_row($selectFileSql, $DB);
			if($selectFile != ''){
				$dest_file = $targetPath . $selectFile['BF_FILE'];
				if ( file_exists($dest_file) ) {
					@unlink($dest_file);
					$deleteSql = " DELETE FROM ".$_HDSCXBB_DB_TABLE['CONTENT_FILES']." WHERE BF_CODE = '".$filesCode."'";
					rf_mysql_query($deleteSql, $DB);
				}
			}
		
	}


	function getHolidayList($sDate,$eDate){
		global $DB,$_HDSCXBB_DB_TABLE,$BB_DATE;
		
		$holidaySql = " SELECT * FROM ".$_HDSCXBB_DB_TABLE['HOLIDAY']." where  date_format(holiday_date, '%Y-%m-%d') >= '".$sDate."' AND date_format(holiday_date, '%Y-%m-%d') <= '".$eDate."' ";
		$holidayData = 	rf_mysql_arr($holidaySql, $DB); 

		return $holidayData;
	}
	

	function sendMessage($pType,$receiver,$title,$msg,$targetSection,$targetCode){
		global $DB,$_HDSCXBB_DB_TABLE,$BB_DATE;
		$s = strtoupper(md5(uniqid("_HDSCXBB_",true))); 
		$code = 
			'M'.
			substr($s,0,3).
			$receiver.
			substr($s,8,4).$BB_DATE;
		$insertSql = " INSERT INTO  ".$_HDSCXBB_DB_TABLE['APP_MESSAGE']." SET
			CODE = '".$code."',
			P_TYPE = '".$pType."',
			TARGET_USER = 'U',
			M_TITLE = '".$title."',
			M_CONTENT = '".$msg."',
			TARGET_SECTION = '".$targetSection."',
			TARGET_CODE = '".$targetCode."',
			USER_CODE = '".$receiver."',
			USE_YN = 'Y',
			REG_DATE = '".$BB_DATE."'
		";
		 rf_mysql_query($insertSql, $DB);

		 $userIds = user($receiver);
		 $registrationIds = array($userIds['DEVICE_TOKEN']);
		 $message = array ("body" => $msg,"title" => $title,"badge"=>0);
		 $targetScreen = $targetSection;
		 $data = array( 
			"show_in_foreground"=> false,
			'priority' => 'high',
			'mode'=>'OPEN',
			"targetScreen"=>$targetSection,
			"targetCode"=>$targetCode,
			'show_in_active'=> false,
			'message' => $message,
			);
		 $type = "data";
		 $fcm_result = fcm($message,$data, $type,$registrationIds);
	}


	function fcm($message, $data, $type, $notification_ids =array()){

		define( 'API_ACCESS_KEY', 'AAAAsvp2h38:APA91bFGJsJEKypYdH8c5OarJQak3THD_NWsZblqnrm506bYxQEP0txwGE1YJpxXugHMrnzpv7fJdpiCDir9KdbfOp-p_oNEUr8oWtrlzCF3oqbqE3UIz1t8GoStsuMwIG9ufdfy0ey_');

		$registrationIds = $notification_ids;
		//$data = [ "type" => $type ];
		if($type == "data"){
			$fields = array(
				'registration_ids'  => $registrationIds,
				'data'=> $data,
				'priority' => 'high',
				"content_available" => true,
				'type' => 'data'
			);

		} else if($type == "noti"){
			$data = ["notificationData" => $message];

			 
			$fields = array(
				'registration_ids'  => $registrationIds,
				'notification'=> $message,
				'data'=> $data,
				'priority' => 'high',
				"content_available" => true,
				'type' => 'noti'
			);

		}

		$headers = array
		(
			'Authorization: key=' . API_ACCESS_KEY,
			'Content-Type: application/json'
		);
		 
		$ch = curl_init();
		curl_setopt( $ch,CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send' );
		curl_setopt( $ch,CURLOPT_POST, true );
		curl_setopt( $ch,CURLOPT_HTTPHEADER, $headers );
		curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER, false );
		curl_setopt( $ch,CURLOPT_POSTFIELDS, json_encode( $fields ) );
		$fcm_result = curl_exec($ch);
		curl_close( $ch );
		return $fcm_result;

	}    

?>