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

        /* ************************************* 영화 예매 관련 ************************************* */
        /*
         * API No. 11
         * API Name : 바로예매(지점 조회) API
         * 마지막 수정 날짜 : 19.04.29
         */
        case "getBranchDirectTicketing":
            http_response_code(200);

            $movie_idx = $vars['movie_idx'];

            if(!isValidMovie($movie_idx)){
                $res->is_success = FALSE;
                $res->code = 201;
                $res->message = "없는 영화 인덱스입니다";
                echo json_encode($res,JSON_NUMERIC_CHECK);
                break;
            }

            $res->result = getBranchDirectTicketing($movie_idx);
            $res->is_success = TRUE;
            $res->code = 100;
            $res->message = "상영 지점 조회 성공(바로 예매 눌렀을 때)";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        /*
         * API No. 12
         * API Name : 바로 예매(관,시간 조회) API
         * 마지막 수정 날짜 : 19.04.29
         */
        case "getTheaterDirectTicketing":
            http_response_code(200);

            $movie_idx = $vars['movie_idx'];
            $branch_idx = $vars['branch_idx'];

            if(!isValidMovie($movie_idx)){
                $res->is_success = FALSE;
                $res->code = 201;
                $res->message = "없는 영화 인덱스입니다";
                echo json_encode($res,JSON_NUMERIC_CHECK);
                break;
            }

            if(!isValidBranch($branch_idx)){
                $res->is_success = FALSE;
                $res->code = 202;
                $res->message = "없는 지점 인덱스입니다";
                echo json_encode($res,JSON_NUMERIC_CHECK);
                break;
            }

            $res->result = getTheaterDirectTicketing($movie_idx, $branch_idx);
            $res->is_success = TRUE;
            $res->code = 100;
            $res->message = "상영 관, 시간 조회 성공(바로 예매 눌렀을 때)";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        /*
         * API No. 13
         * API Name : 극장별 예매(지점 조회) API
         * 마지막 수정 날짜 : 19.04.29
         */
        case "getBranchTicketing":
            http_response_code(200);


            $res->result = getBranchTicketing();
            $res->is_success = TRUE;
            $res->code = 100;
            $res->message = "극장별 예매 조회 성공(극장별 예매 눌렀을 때)";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;


        /*
         * API No. 14
         * API Name : 극장별 예매(관,시간 조회) API
         * 마지막 수정 날짜 : 19.04.29
         */
        case "getTheaterBranchTicketing":
            http_response_code(200);

            $branch_idx = $vars['branch_idx'];

            if(!isValidBranch($branch_idx)){
                $res->is_success = FALSE;
                $res->code = 201;
                $res->message = "없는 지점 인덱스입니다";
                echo json_encode($res,JSON_NUMERIC_CHECK);
                break;
            }

            $res->result = getTheaterBranchTicketing($branch_idx);
            $res->is_success = TRUE;
            $res->code = 100;
            $res->message = "상영 관, 시간 조회 성공(극장별 예매 눌렀을 때)";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        /*
         * API No. 15
         * API Name : 해당 영화관 남은 좌석 조회 API
         * 마지막 수정 날짜 : 19.04.29
         */
        case "getRestSeat":
            http_response_code(200);

            $theater_info_idx = $vars['theater_info_idx'];

            if(!isValidTheaterInfo($theater_info_idx)){
                $res->is_success = FALSE;
                $res->code = 201;
                $res->message = "없는 관,시간 정보입니다";
                echo json_encode($res,JSON_NUMERIC_CHECK);
                break;
            }

            $res->result = getRestSeat($theater_info_idx);
            $res->is_success = TRUE;
            $res->code = 100;
            $res->message = "해당 영화관 남은 좌석 조회 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;


        /*
         * API No. 16
         * API Name : 좌석 선택 API
         * 마지막 수정 날짜 : 19.04.29
         */
        case "putSeat":
            http_response_code(200);

            $jwt = $_SERVER['HTTP_X_ACCESS_TOKEN'];
            $user_idx = getDataByJWToken($jwt, JWT_SECRET_KEY)->userIdx;
            $theater_info_idx = $req->theater_info_idx;
            $seat = $req->seat;

            if(!isset($jwt) || $jwt == null){
                $res->is_success = FALSE;
                $res->code = 201;
                $res->message = "로그인 후 이용가능한 서비스입니다(jwt를 header에 입력하세요)";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            if(!isValidHeader($jwt, JWT_SECRET_KEY)) {
                $res->is_success = FALSE;
                $res->code = 202;
                $res->message = "잘못된 토큰입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            if(!isValidTheaterInfo($theater_info_idx)){
                $res->is_success = FALSE;
                $res->code = 203;
                $res->message = "없는 관,시간 정보입니다";
                echo json_encode($res,JSON_NUMERIC_CHECK);
                break;
            }

            if(!isValidSameSeat($theater_info_idx, $seat)){
                $res->is_success = FALSE;
                $res->code = 204;
                $res->message = "중복된 좌석 선택입니다";
                echo json_encode($res,JSON_NUMERIC_CHECK);
                break;
            }

            $res->result = putSeat($user_idx,$theater_info_idx,$seat);
            $res->is_success = TRUE;
            $res->code = 100;
            $res->message = "해당 영화관 좌석 선택 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        /*
         * API No. 17
         * API Name : 예매 확인 조회 API
         * 마지막 수정 날짜 : 19.04.29
         */
        case "getTicket":
            http_response_code(200);

            $jwt = $_SERVER['HTTP_X_ACCESS_TOKEN'];

            if(!isset($jwt) || $jwt == null){
                $res->is_success = FALSE;
                $res->code = 201;
                $res->message = "로그인 후 이용가능한 서비스입니다(jwt를 header에 입력하세요)";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            if(!isValidHeader($jwt, JWT_SECRET_KEY)){
                $res->is_success = FALSE;
                $res->code = 202;
                $res->message = "잘못된 토큰입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            $user_idx = getDataByJWToken($jwt, JWT_SECRET_KEY)->userIdx;

            $res->result = getTicket($user_idx);
            $res->is_success = TRUE;
            $res->code = 100;
            $res->message = "예매 조회 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

    }
} catch (\Exception $e) {
    return getSQLErrorException($errorLogs, $e, $req);
}
