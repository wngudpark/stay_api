<?php
    include_once($_SERVER['DOCUMENT_ROOT'].'/lib/common.php');

    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    if (isset($data['data']) && is_array($data['data'])) {
        $answers = $data['data'];

        try {
            // SQL 준비
            $sql = "INSERT INTO survey_response (user_code,question_id, choice_id, area_code, text_answer, url) VALUES (?,?, ?, ?, ?, ?)";
            $result = $DB->prepare($sql);
            $randomString = randomString(10);
            // 각 답변을 데이터베이스에 삽입
            foreach ($answers as $value) {
                // 데이터 바인딩 및 실행
                $result->execute([
                    $randomString,
                    $value['question_id'],
                    $value['choice_id'],
                    $value['area_code'],
                    $value['text_answer'],
                    $value['url']
                ]);
            }

            // 응답을 클라이언트로 전송
            $response = [
                'success' => true,
                'message' => 'success'
            ];
            echo json_encode($response);

        } catch (Exception $e) {
            // 에러 처리
            $response = [
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage()
            ];
            echo json_encode($response);
        }
    } else {
        // 잘못된 데이터가 전송된 경우
        $response = [
            'success' => false,
            'message' => 'Invalid data format.'
        ];
        echo json_encode($response);
    }

?>
