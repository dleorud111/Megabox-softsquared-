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
         * API No. 4
         * API Name : 테스트 API
         * 마지막 수정 날짜 : 19.04.29
         */
        case "getUsers":
            http_response_code(200);

            $res->result = getUsers();
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "테스트 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
        /*
         * API No. 5
         * API Name : 테스트 Path Variable API
         * 마지막 수정 날짜 : 19.04.29
         */
        case "getUserDetail":
            http_response_code(200);

            $res->result = getUserDetail($vars["userIdx"]);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "테스트 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
        /*
         * API No. 6
         * API Name : 테스트 Body & Insert API
         * 마지막 수정 날짜 : 19.04.29
         */
//        case "createUser":
//            http_response_code(200);
//
//            // Packet의 Body에서 데이터를 파싱합니다.
//            $userID = $req->userID;
//            $pwd_hash = password_hash($req->pwd, PASSWORD_DEFAULT); // Password Hash
//            $name = $req->name;
//
//            $res->result = createUser($userID, $pwd_hash, $name);
//            $res->isSuccess = TRUE;
//            $res->code = 100;
//            $res->message = "테스트 성공";
//            echo json_encode($res, JSON_NUMERIC_CHECK);
//            break;



        /*
         * API No. 1
         * API Name : 자체 로그인 API
         * 마지막 수정 날짜 : 19.04.29
         */

        case "login":
            http_response_code(200);

            $accessToken = $req->accessToken;
            $header = "Bearer ".$accessToken;
            $url = "https://openapi.naver.com/v1/nid/me";
            $is_post = false;

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, $is_post);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $headers = array();
            $headers[] = "Authorization: ".$header;
            curl_setopt($ch, CURLOPT_HTTPHEADER,$headers);

            $response = curl_exec($ch);
            $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            //echo "status_code:" . $status_code;

            curl_close($ch);


            if ($status_code == 200) {
                $profileResponse = json_decode($response);
                $server_id = $profileResponse->response->id;

                if (!isValidNaverUser($server_id)) {
                    $res->result["jwt"] = $server_id;  //원래 "server_id"인데 클라 요구로 jwt로 변경
                    $res->is_success = FALSE;
                    $res->code = 201;
                    $res->message = "존재하지 않는 유저입니다. 회원가입을 하세요";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    break;
                }
                $userIdx = getUserIdxByID($server_id);
                $jwt = getJWT($userIdx, JWT_SECRET_KEY);

                $res->result["jwt"] = $jwt;
                $res->is_success = TRUE;
                $res->code = 100;
                $res->message = "로그인 성공";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;

            } else {
                $res->is_success = FALSE;
                $res->code = 202;
                $res->message = "인증되지 않은 토큰입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

        /*
         * API No. 2
         * API Name : 회원가입 API
         * 마지막 수정 날짜 : 19.04.29
         */

        case "postUser":
            http_response_code(200);

            if (isValidId($req->id)) {
                $res->is_success = FALSE;
                $res->code = 201;
                $res->message = "아이디 중복입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            else if (isValidServerId($req->server_id)) {
                $res->is_success = FAlSE;
                $res->code = 202;
                $res->message = "존재하는 서버 아이디입니다.서버 아이디값을 확인하세요";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            else if (!isValidPhone($req->phone)) {
                $res->is_success = FAlSE;
                $res->code = 203;
                $res->message = "잘못된 핸드폰 번호 형식입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            } else if (!isValidBirth($req->birth)) {
                $res->is_success = FAlSE;
                $res->code = 204;
                $res->message = "잘못된 생년월일 형식입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            $id = $req->id;
            $server_id = $req->server_id;
            $name = $req->name;
            $phone = $req->phone;
            $birth = $req->birth;

            postUser($id, $server_id, $name, $phone, $birth);
            $res->is_success = TRUE;
            $res->code = 100;
            $res->message = "회원가입 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

/* ************************************* 영화 정보 관련 ************************************* */
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
                $res->message = "잘못된 해시태그입니다";
                echo json_encode($res,JSON_NUMERIC_CHECK);
            }
            break;


        /*
         * API No. 4
         * API Name : 영화 간단 소개 API
         * 마지막 수정 날짜 : 19.04.29
         */
        case "getMovieIntro":
            http_response_code(200);

            $movie_idx = $vars['movie_idx'];

            if(!isValidMovie($movie_idx)){
                $res->is_success = FALSE;
                $res->code = 201;
                $res->message = "없는 영화 인덱스입니다";
                echo json_encode($res,JSON_NUMERIC_CHECK);
                break;
            }

            $res->result = getMovieIntro($movie_idx);
            $res->is_success = TRUE;
            $res->code = 100;
            $res->message = "영화 간단 소개 성공";
            echo json_encode($res,JSON_NUMERIC_CHECK);


            break;

        /*
         * API No. 5
         * API Name : 영화 상세 정보 조회 API
         * 마지막 수정 날짜 : 19.04.29
         */
        case "getMovieInfo":
            http_response_code(200);

            $movie_idx = $vars['movie_idx'];

            if(!isValidMovie($movie_idx)){
                $res->is_success = FALSE;
                $res->code = 201;
                $res->message = "없는 영화 인덱스입니다";
                echo json_encode($res,JSON_NUMERIC_CHECK);
                break;
            }

            $res->result = getMovieInfo($movie_idx);
            $res->is_success = TRUE;
            $res->code = 100;
            $res->message = "영화 상세정보 조회 성공";
            echo json_encode($res);
            break;

        /*
         * API No. 9
         * API Name : 영화 보고싶어 누르기 API
         * 마지막 수정 날짜 : 19.04.29
         */
        case "chgMovieHeart":
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
            $movie_idx = $vars['movie_idx'];

            $res->result = chgMovieHeart($user_idx, $movie_idx);
            $res->is_success = TRUE;
            $res->code = 100;
            $res->message = "영화 보고싶어 누르기 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        /*
         * API No. 10
         * API Name : 극장 좋아요 누르기 API
         * 마지막 수정 날짜 : 19.04.29
         */
        case "chgBranchLike":
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
            $branch_idx = $vars['branch_idx'];

            $res->result = chgBranchLike($user_idx, $branch_idx);
            $res->is_success = TRUE;
            $res->code = 100;
            $res->message = "선호 극장 누르기 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
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
         * API Name : 바로예매(지점 조회) API
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
                $res->code = 202;
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








    }
} catch (\Exception $e) {
    return getSQLErrorException($errorLogs, $e, $req);
}
