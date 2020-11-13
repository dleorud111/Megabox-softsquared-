<?php

//READ
function getUsers()
{
    $pdo = pdoSqlConnect();
    $query = "select * from USER;";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

//READ
function getUserDetail($userIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select * from Users where userIdx = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$userIdx]);
    //    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0];
}

//READ
function isValidUserIdx($userIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select EXISTS(select * from Users where userIdx = ?) exist;";

    $st = $pdo->prepare($query);
    $st->execute([$userIdx]);
    //    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0]['exist'];
}

/* ****************************************** 로그인 관련 함수 ****************************************** */

// 네이버 회원확인
function isValidNaverUser($server_id)
{
    $pdo = pdoSqlConnect();
    $query = "select exists(select server_id from USER where server_id = ?) as exist;";

    $st = $pdo->prepare($query);
    $st->execute([$server_id]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();
    $st = null;
    $pdo = null;

    return intval($res[0]["exist"]);

}

// 회원가입
function postUser($id, $server_id, $name, $phone, $birth)
{
    $pdo = pdoSqlConnect();
    $query = "INSERT INTO USER (id,server_id,name,phone,birth,created_at) VALUES (?,?,?,?,?,now());";

    $st = $pdo->prepare($query);
    $st->execute([$id, $server_id, $name, $phone, $birth]);

    $st = null;
    $pdo = null;

}

// 아이디 중복확인
function isValidId($id)
{
    $pdo = pdoSqlConnect();
    $query = "select exists(select id from USER where id = ?) as exist;";

    $st = $pdo->prepare($query);
    $st->execute([$id]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();
    $st = null;
    $pdo = null;

    return intval($res[0]["exist"]);

}
// server_id 중복 확인
function isValidServerId($server_id)
{
    $pdo = pdoSqlConnect();
    $query = "select exists(select server_id from USER where server_id = ?) as exist;";

    $st = $pdo->prepare($query);
    $st->execute([$server_id]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();
    $st = null;
    $pdo = null;

    return intval($res[0]["exist"]);

}

// 핸드폰 번호 형식 확인
function isValidPhone($phone){
    return  preg_match("/^01[0-9]{8,9}$/", $phone); //01로 시작
}

// 생일 형식 확인
function isValidBirth($birth){
    return  preg_match("/^([0-9]{2}(0[1-9]|1[0-2])(0[1-9]|[1,2][0-9]|3[0,1]))$/", $birth); //6자리 생년월일
}

//theater_info_idx 찾기
function getTheaterInfoIdx($branch, $theater, $date, $time)
{
    $pdo = pdoSqlConnect();
    $query = "select theater_info_idx, BRANCH.branch_name, concat(theater_idx,'관') as theater,
                     concat(date_format(date, '%Y'),'.',date_format(date, '%m'),'.',date_format(date, '%d')) as start_day,
                     time_format(start_time, '%H:%i') as start_time
              from THEATER_INFO, BRANCH
              where THEATER_INFO.branch_idx = BRANCH.branch_idx and  branch_name = ? and theater_idx = ? and date = ? and
                    start_time = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$branch,$theater,$date,$time]);
    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

// 회원 이름 아이디 조회
function getUserInfo($uesr_idx)
{
    $pdo = pdoSqlConnect();
    $query = "select name, id from USER where idx = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$uesr_idx]);
    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

// 푸쉬 알림
function pushAlarm($fcm_token)
{
    $server_key = "AAAA3wlKw4U:APA91bGm4GWmI-XfVUHUv8GXGwqaBQst0f8tlBlkqdfWiSMK5M26PkhoIQBHHYM0xsag5IHSVCl5-mNCMp9euqWiE3zdCyyebyTjgY-CSc0F21bOGFCD5w3ppPx4tsTFFCaZ2S1ttBR3";
    $title = "영화 상영 정보";
    $body = "영화 상영 15분 전입니다.";
    $notification = array('title' => $title, 'body' => $body, 'sound' => 'default', 'badge' => '1');
    $arrayToSend = array('to' => $fcm_token, 'notification' => $notification, 'priority' => 'high');
    $json = json_encode($arrayToSend);
    $headers = array('Authorization: key=' . $server_key, 'Content-Type: application/json');
    $url = 'https://fcm.googleapis.com/fcm/send';

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $result = curl_exec($ch);
    curl_close($ch);

    return json_decode($result, true);
}