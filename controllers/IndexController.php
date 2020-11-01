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
        case "createUser":
            http_response_code(200);

            // Packet의 Body에서 데이터를 파싱합니다.
            $userID = $req->userID;
            $pwd_hash = password_hash($req->pwd, PASSWORD_DEFAULT); // Password Hash
            $name = $req->name;

            $res->result = createUser($userID, $pwd_hash, $name);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "테스트 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        /*
         * API No. 1
         * API Name : 회원가입 API
         * 마지막 수정 날짜 : 19.04.29
         */
        case "postUser":
            http_response_code(200);

            if(isValidId($req->id)){
                $res->isSuccess = FALSE;
                $res->code = 201;
                $res->message = "아이디 중복입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
            }
            else if(!isValidPassword($req->pwd)){
                $res->isSuccess = FALSE;
                $res->code = 202;
                $res->message = "잘못된 비밀번호 형식입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
            }
            else if(!isValidPhone($req->phone)){
                $res->isSuccess = FAlSE;
                $res->code = 203;
                $res->message = "잘못된 핸드폰 번호 형식입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
            }
            else if(!isValidBirth($req->birth)){
                $res->isSuccess = FAlSE;
                $res->code = 204;
                $res->message = "잘못된 생년월일 형식입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
            }

            $id = $req->id;
            $pwd_hash= password_hash($req->pwd, PASSWORD_DEFAULT); // Password Hash
            $name = $req->name;
            $phone = $req->phone;
            $birth = $req->birth;

            $res->result = postUser($id, $pwd_hash, $name, $phone, $birth);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "회원가입 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
    }
} catch (\Exception $e) {
    return getSQLErrorException($errorLogs, $e, $req);
}
