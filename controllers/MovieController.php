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
                echo json_encode($res, JSON_NUMERIC_CHECK);
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
         * API No. 6
         * API Name : 영화 실관람평 조회 API
         * 마지막 수정 날짜 : 19.04.29
         */
        case "getMovieReview":
            http_response_code(200);

            $movie_idx = $vars['movie_idx'];

            if(!isValidMovie($movie_idx)){
                $res->is_success = FALSE;
                $res->code = 201;
                $res->message = "없는 영화 인덱스입니다";
                echo json_encode($res,JSON_NUMERIC_CHECK);
                break;
            }

            $res->result = getMovieReview($movie_idx);
            $res->is_success = TRUE;
            $res->code = 100;
            $res->message = "영화 실관람평 조회 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;


        /*
         * API No. 7
         * API Name : 영화 실관람평 쓰기 API
         * 마지막 수정 날짜 : 19.04.29
         */
        case "postMovieReview":
            http_response_code(200);

            $movie_idx = $vars['movie_idx'];

            $jwt = $_SERVER['HTTP_X_ACCESS_TOKEN'];
            $user_idx = getDataByJWToken($jwt, JWT_SECRET_KEY)->userIdx;

            $star = $req->star;
            $comment = $req->comment;

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

            if(!isValidMovie($movie_idx)){
                $res->is_success = FALSE;
                $res->code = 203;
                $res->message = "없는 영화 인덱스입니다";
                echo json_encode($res,JSON_NUMERIC_CHECK);
                break;
            }

            if(!isValidPostReviewWatched($movie_idx, $user_idx)){
                $res->is_success = FALSE;
                $res->code = 204;
                $res->message = "리뷰를 쓸 권한이 없습니다(영화를 시청하지 않았습니다)";
                echo json_encode($res,JSON_NUMERIC_CHECK);
                break;
            }

            if(isValidPostReviewDone($movie_idx, $user_idx)){
                $res->is_success = FALSE;
                $res->code = 205;
                $res->message = "이미 이 영화에 대해 리뷰를 작성했습니다";
                echo json_encode($res,JSON_NUMERIC_CHECK);
                break;
            }

            postMovieReview($movie_idx, $user_idx, $star, $comment);
            $res->is_success = TRUE;
            $res->code = 100;
            $res->message = "영화 실관람평 작성 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        /*
         * API No. 8
         * API Name : 영화 실관람평에 좋아요 API
         * 마지막 수정 날짜 : 19.04.29
         */
        case "chgReviewLike":
            http_response_code(200);

            $jwt = $_SERVER['HTTP_X_ACCESS_TOKEN'];
            $user_idx = getDataByJWToken($jwt, JWT_SECRET_KEY)->userIdx;
            $review_idx = $vars['review_idx'];

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


            $res->result = chgReviewLike($user_idx, $review_idx);
            $res->is_success = TRUE;
            $res->code = 100;
            $res->message = "영화 실관람평에 좋아요 누르기 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
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

    }
} catch (\Exception $e) {
    return getSQLErrorException($errorLogs, $e, $req);
}
