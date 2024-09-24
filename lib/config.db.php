<?php

$db_addr = 'localhost';
$db_id = 'root';
$db_pass = '';
$db_sel = 'another_survey';

$DB = mysqli_connect($db_addr, $db_id, $db_pass, $db_sel, 3306)or die("Fail connect DataBase Server 2");
mysqli_select_db($DB,$db_sel) or die("Could not select db 1");
mysqli_query($DB,'SET NAMES UTF8') or die("Error Setting db 1");


//**********************************************************************************************************
// BB DB table
// DB test table : []_test
// DB table : []
//**********************************************************************************************************



// $_HDSCXBB_DB_TABLE['ADMIN_MEMBER'] = " hds_admin_member ";


// $_HDSCXBB_DB_TABLE['CONTENT_LANGUAGE_SELECT'] = " hds_content_laguage_select ";


// $_HDSCXBB_DB_TABLE['CONTENT_FILES'] = " hds_content_files ";
// $_HDSCXBB_DB_TABLE['CONTENT_MAIN'] = " hds_content_main ";

// $_HDSCXBB_DB_TABLE['CONTENT_COMMENTARY'] = " hds_content_commentary ";

// $_HDSCXBB_DB_TABLE['CONTENT_COMMENTARY_ITEM'] = " hds_content_commentary_item ";

// $_HDSCXBB_DB_TABLE['CONTENT_SURVEY'] = " hds_survey ";
// $_HDSCXBB_DB_TABLE['CONTENT_SURVEY_RESULT'] = " hds_survey_result ";


// $_HDSCXBB_DB_TABLE['LOGS'] = " hds_logs ";
// $_HDSCXBB_DB_TABLE['HOLIDAY'] = " hds_holiday ";

// $_HDSCXBB_DB_TABLE['SPOT']  = " hds_spot ";
// $_HDSCXBB_DB_TABLE['COURSE']  = " hds_course ";

?>