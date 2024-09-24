<?php
include_once($_SERVER['DOCUMENT_ROOT'].'/lib/common.php');

// // 클라이언트에서 전달된 URL을 쿼리 파라미터에서 받기
// $rootUrl = isset($_GET['rootUrl']) ? $_GET['rootUrl'] : null;

// // 기본값으로 page_name을 localhost로 설정
// $page_name = 'localhost';

// // 특정 URL에 따라 page_name 설정
// if ($rootUrl && (strpos($rootUrl, '/house') !== false || 
//     strpos($rootUrl, '/') !== false || 
//     strpos($rootUrl, '/house3') !== false)) {
//     $page_name = 'localhost2';
// }

// 클라이언트에서 전달된 URL을 쿼리 파라미터에서 받기
$rootUrl = isset($_GET['rootUrl']) ? $_GET['rootUrl'] : null;

// 기본값으로 page_name을 localhost로 설정
$page_name = 'localhost';

if ($rootUrl) {
    // URL을 파싱하여 호스트와 포트 정보를 가져오기
    $parsedUrl = parse_url($rootUrl);
    $host = isset($parsedUrl['host']) ? $parsedUrl['host'] : '';
    $port = isset($parsedUrl['port']) ? ':' . $parsedUrl['port'] : '';

    // 호스트와 포트를 결합한 URL
    $fullHost = $host . $port;

    // 특정 URL에 따라 page_name 설정
    if (strpos($fullHost, 'localhost:8080') !== false ||
        strpos($fullHost, 'localhost:3002') !== false ||
        strpos($rootUrl, '/house') !== false || 
        strpos($rootUrl, '/house3') !== false) {
        $page_name = 'localhost2';
    }
}

// 이후에 $page_name을 사용하여 로직 수행


// SQL 쿼리에서 page_name 사용
$sql = "SELECT id, question_order FROM survey_question_set WHERE page_name = '$page_name'";
$result = rf_mysql_arr($sql, $DB);
$id = $result[0]["id"];

$q_sql = "SELECT id,question FROM survey_question";
$q_result = rf_mysql_arr($q_sql, $DB);

$questions = [];
foreach($q_result as $key => $value) {
    $questions[$value["id"]] = $value["question"];
}

$sql="SELECT
    sqs.id AS question_set_id,
    sq.id AS question_id,
    sq.question,
    sq.multiple_yn as multiple_yn,
    sq.type as type,
    sq.type,
    sc.id AS choice_id,
    sc.choice,
    sc.etc_yn as etc_yn
    FROM survey_question_set AS sqs
    INNER JOIN survey_question AS sq ON FIND_IN_SET(sq.id, sqs.question_order) > 0
    LEFT JOIN survey_choice AS sc ON sq.id = sc.question_id
    WHERE sqs.id = '$id' 
    ORDER BY FIND_IN_SET(sq.id, sqs.question_order)";

$result = rf_mysql_arr($sql, $DB);

$data = [];
$lastQuestionId = null;
foreach($result as $value){
    $c_question_id = $value["question_id"];
    $c_question = $value["question"];
    $multiple_yn = $value["multiple_yn"];
    $type = $value["type"];
    $etc_yn = $value["etc_yn"];
    $choice = $value["choice"];

    if($lastQuestionId !== $c_question_id) {
        // Create new question with first choice
        if (isset($questions[$c_question_id])) {
            $data[] = [
                "question_id" => $c_question_id,
                "multiple_yn" => $multiple_yn,
                "type" => $type,
                "question" => $questions[$c_question_id],
                "choices" => [
                    ["choice_id" => $value["choice_id"], "choice" => $choice, "etc_yn" => $etc_yn]
                ]
            ];
        }
        $lastQuestionId = $c_question_id;
    } else {
        // Add choice to the last question
        $lastQuestionIndex = count($data) - 1;
        $data[$lastQuestionIndex]["choices"][] = ["choice_id" => $value["choice_id"], "choice" => $choice,"etc_yn" => $etc_yn];
    }
}

$area_sql = "SELECT area_code, area FROM survey_location ORDER BY idx";
$a_result = rf_mysql_arr($area_sql, $DB);

foreach($a_result as $value){
    $areaData[] = array('area_code' => $value['area_code'], 'area' => $value['area']);
}

$apiData = array(
    "success" => true,  // 요청 성공 여부
    "message" => $rootUrl,  // 추가적인 메시지
    "questions" => $data,
    "areaData" => $areaData
);
  

header('Content-Type: application/json');
echo json_encode($apiData, JSON_UNESCAPED_UNICODE);
?>
