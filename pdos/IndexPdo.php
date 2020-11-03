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


/* ****************************************** 영화관련 함수 ****************************************** */

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

// 영화 인덱스 확인
function isValidMovie($movie_idx){
    $pdo = pdoSqlConnect();
    $query = "select exists(select movie_idx from MOVIE where movie_idx = ?) as exist;";

    $st = $pdo->prepare($query);
    $st->execute([$movie_idx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();
    $st = null;
    $pdo = null;

    return intval($res[0]["exist"]);
}

// 영화 간단 소개
function getMovieIntro($movie_idx){
    $pdo = pdoSqlConnect();
    $query = "with MOVIE_RANK as (select @rank := @rank + 1 as ranking, movie_idx from MOVIE,(SELECT @rank := 0) r)
              select MOVIE.movie_idx, poster,
                     case when datediff(start_day, curdate()) > 0 then concat('D-',datediff(start_day, curdate()))
                          when datediff(start_day, curdate()) < 0 then '상영중'
                     end as movie_status,
                     hash_tag, k_name, e_name, grade, concat(MOVIE_RANK.ranking, '위') as ranking,
                     concat('(',reservation,'%)') as reservation, description
              from MOVIE, MOVIE_RANK
              where MOVIE.movie_idx = MOVIE_RANK.movie_idx and MOVIE.movie_idx = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$movie_idx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();
    $st = null;
    $pdo = null;

    return $res;
}

// 영화 상세정보 조회
function getMovieInfo($movie_idx){
    $pdo = pdoSqlConnect();
    $query = "select MOVIE.movie_idx, concat(date_format(start_day, '%Y'),'.',date_format(start_day, '%m'),
                                            '.',date_format(start_day, '%d')) as start_day,
                     type, concat(genre,' / ',running_time) as genre, grade, producer, performer,
                     case when audience = 0 then cast(audience as char(10))
                          when audience < 10000 then concat(audience div 1000, ',' , audience mod 1000)
                          when audience >= 10000 then concat(audience div 10000, '.',
                                                            left(cast(audience mod 10000 as char(10)) ,1),'만')
                          end as total_audience,
                          if(today = 0, cast(today as char(10)), concat(today div 1000, ',' , today mod 1000)) as today_audience,
                          if(today = 0, cast(today as char(10)), concat(truncate(((today-last_day)/last_day)*100,1),'%')) as ratio_last_day,
                          case when curdate() > start_day then concat('개봉 ',datediff(curdate(),start_day),'일차')
                               when curdate() < start_day then null
                          end as how_long_date
              from MOVIE, DAY_AUDIENCE
              where MOVIE.movie_idx = DAY_AUDIENCE.movie_idx and  MOVIE.movie_idx = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$movie_idx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();
    $st = null;
    $pdo = null;

    return $res;
}