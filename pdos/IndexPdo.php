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
