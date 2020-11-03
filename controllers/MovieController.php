<?php
require 'function.php';

const JWT_SECRET_KEY = "TEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEY";

$res = (object)array();
header('Content-Type: json');
$req = json_decode(file_get_contents("php://input"));
try {
    addAccessLogs($accessLogs, $req);
    switch ($handler) {
        case "index":
            echo "API Server";
            break;
        case "ACCESS_LOGS":
            //            header('content-type text/html charset=utf-8');
            header('Content-Type: text/html; charset=UTF-8');
            getLogs("./logs/access.log");
            break;
        case "ERROR_LOGS":
            //            header('content-type text/html charset=utf-8');
            header('Content-Type: text/html; charset=UTF-8');
            getLogs("./logs/errors.log");
            break;

        /*
         * API No. 3
         * API Name : 메인화면 영화 나열 API
         * 마지막 수정 날짜 : 19.04.29
         */
        case "getMovies":
            http_response_code(200);

            $hash_tag = $_GET["hash-tag"];

            if($hash_tag == "필소"){
                $hash_tag = "필름소사이어티";
            }

            if($hash_tag=="박스오피스"){
                $res->result = getMovies();
                $res->is_success = TRUE;
                $res->code = 100;
                $res->message = "영화 순위 나열 성공";
                echo json_encode($res,JSON_NUMERIC_CHECK);
            }
            else if($hash_tag=='상영예정' || $hash_tag=='빵원티켓' || $hash_tag=='단독' || $hash_tag=='오리지널티켓'
                || $hash_tag=='필름소사이어티'){
                $res->result = getHashTagMovies($hash_tag);
                $res->is_success = TRUE;
                $res->code = 101;
                $res->message = "해시태그별 영화 나열 성공";
                echo json_encode($res,JSON_NUMERIC_CHECK);
            }
            else{
                $res->is_success = FALSE;
                $res->code = 201;
                $res->message = "해시태그를 확인하세요";
                echo json_encode($res,JSON_NUMERIC_CHECK);
            }

            break;

    }
} catch (\Exception $e) {
    return getSQLErrorException($errorLogs, $e, $req);
}
