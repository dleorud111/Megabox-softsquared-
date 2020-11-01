<?php

//READ
function getUsers()
{
    $pdo = pdoSqlConnect();
    $query = "select * from Users;";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([]);
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


function createUser($ID, $pwd, $name)
{
    $pdo = pdoSqlConnect();
    $query = "INSERT INTO Users (ID, pwd, name) VALUES (?,?,?);";

    $st = $pdo->prepare($query);
    $st->execute([$ID, $pwd, $name]);

    $st = null;
    $pdo = null;

}

// 회원가입
function postUser($id, $pwd_hash, $name, $phone, $birth)
{
    $pdo = pdoSqlConnect();
    $query = "INSERT INTO USER (id,pwd,name,phone,birth) VALUES (?,?,?,?,?);";

    $st = $pdo->prepare($query);
    $st->execute([$id, $pwd_hash, $name, $phone, $birth]);

    $st = null;
    $pdo = null;

}

// 아이디 중복확인
function isValidUId($id)
{
    $pdo = pdoSqlConnect();
    $query = "select exists(select id from USER where id = ?) as exist;";

    $st = $pdo->prepare($query);
    $st->execute([$id]);
    $res = $st->fetchAll();
    $st = null;
    $pdo = null;

    return intval($res[0]["exist"]);

}


// CREATE
//    function addMaintenance($message){
//        $pdo = pdoSqlConnect();
//        $query = "INSERT INTO MAINTENANCE (MESSAGE) VALUES (?);";
//
//        $st = $pdo->prepare($query);
//        $st->execute([$message]);
//
//        $st = null;
//        $pdo = null;
//
//    }


// UPDATE
//    function updateMaintenanceStatus($message, $status, $no){
//        $pdo = pdoSqlConnect();
//        $query = "UPDATE MAINTENANCE
//                        SET MESSAGE = ?,
//                            STATUS  = ?
//                        WHERE NO = ?";
//
//        $st = $pdo->prepare($query);
//        $st->execute([$message, $status, $no]);
//        $st = null;
//        $pdo = null;
//    }

// RETURN BOOLEAN
//    function isRedundantEmail($email){
//        $pdo = pdoSqlConnect();
//        $query = "SELECT EXISTS(SELECT * FROM USER_TB WHERE EMAIL= ?) AS exist;";
//
//
//        $st = $pdo->prepare($query);
//        //    $st->execute([$param,$param]);
//        $st->execute([$email]);
//        $st->setFetchMode(PDO::FETCH_ASSOC);
//        $res = $st->fetchAll();
//
//        $st=null;$pdo = null;
//
//        return intval($res[0]["exist"]);
//
//    }
