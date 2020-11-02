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


//function createUser($ID, $pwd, $name)
//{
//    $pdo = pdoSqlConnect();
//    $query = "INSERT INTO Users (ID, pwd, name) VALUES (?,?,?);";
//
//    $st = $pdo->prepare($query);
//    $st->execute([$ID, $pwd, $name]);
//
//    $st = null;
//    $pdo = null;
//
//}

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
// 비밀번호 형식 확인
function isValidPassword($password){

    //영문,숫자,특수문자 중 2가지 이상 조합 10자리 이상(특수문자는 ~!@$%^*+=-?_허용)
    if(preg_match("/^.*(?=^.{10,}$)(?=.*[a-zA-Z])(?=.*[0-9])(?=.*[~!@$%^*+=-?_]).*$/", $password)
        ||preg_match("/^.*(?=^.{10,}$)(?=.*[a-zA-Z])(?=.*[0-9]).*$/", $password)
        ||preg_match("/^.*(?=^.{10,}$)(?=.*[a-zA-Z])(?=.*[~!@$%^*+=-?_]).*$/", $password)
        ||preg_match("/^.*(?=^.{10,}$)(?=.*[0-9])(?=.*[~!@$%^*+=-?_]).*$/", $password)){
        return true;
    }
    else{
        return false;
    }
}

// 핸드폰 번호 형식 확인
function isValidPhone($phone){
    return  preg_match("/^01[0-9]{8,9}$/", $phone); //01로 시작
}

// 생일 형식 확인
function isValidBirth($birth){
    return  preg_match("/^([0-9]{2}(0[1-9]|1[0-2])(0[1-9]|[1,2][0-9]|3[0,1]))$/", $birth); //6자리 생년월일
}

// 영화 순위 나열
function getMovies()
{
    $pdo = pdoSqlConnect();
    $query = "select @rank := @rank + 1 as ranking, movie_idx, poster,
                        case when grade='전체관람가' then 'ALL'
                        when grade='12세이상관람가' then '12'
                        when grade='15세이상관람가' then '15'
                        when grade='청소년관람불가' then '청불'
                        end as grade,
                    k_name, reservation,
                        case when datediff(start_day, curdate()) > 0 then concat('D-',datediff(start_day, curdate()))
                        when datediff(start_day, curdate()) < 0 then null
                        end as start_day,
                        case when datediff(start_day, curdate()) < 0 then star
                        when datediff(start_day, curdate()) > 0 then null
                        end as star,
                    zzim
             from MOVIE, (SELECT @RANK := 0) r
             order by reservation desc;";

    $st = $pdo->prepare($query);
    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();
    $st = null;
    $pdo = null;

    return $res;
}

// 영화 순위 나열(해시태그 필터)
function getHashTagMovies($hash_tag)
{
    $pdo = pdoSqlConnect();
    $query = "select @rank := @rank + 1 as ranking, movie_idx, poster,
                        case when grade='전체관람가' then 'ALL'
                        when grade='12세이상관람가' then '12'
                        when grade='15세이상관람가' then '15'
                        when grade='청소년관람불가' then '청불'
                        end as grade,
                    k_name, reservation,
                        case when datediff(start_day, curdate()) > 0 then concat('D-',datediff(start_day, curdate()))
                        when datediff(start_day, curdate()) < 0 then null
                        end as start_day,
                        case when datediff(start_day, curdate()) < 0 then star
                        when datediff(start_day, curdate()) > 0 then null
                        end as star,
                    zzim
             from MOVIE, (SELECT @RANK := 0) r
             where hash_tag regexp ?
             order by reservation desc;";

    $st = $pdo->prepare($query);
    $st->execute([$hash_tag]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();
    $st = null;
    $pdo = null;

    return $res;
}
