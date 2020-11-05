<?php

/* ****************************************** 영화 정보 관련 함수 ****************************************** */

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

// JWT 토큰 유효성 검사
function isValidHeader($jwt, $key)
{
    try{
        $id = getDataByJWToken($jwt, $key)->userIdx;
        return isValidUserJWT($id);
    } catch (\Exception $e) {
        return false;
    }
}

// 유저 유효성 검사
function isValidUserJWT($id)
{
    $pdo = pdoSqlConnect();
    $query = "select exists(select idx from USER where idx = ?) as exist;";

    $st = $pdo->prepare($query);
    $st->execute([$id]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();
    $st = null;
    $pdo = null;

    return intval($res[0]["exist"]);
}

// 영화 보고싶어 누르기
function chgMovieHeart($user_idx, $movie_idx){
    $pdo = pdoSqlConnect();
    try{
        $pdo->beginTransaction();

        $query = "update ZZIM set status = if(status=1,0,1) where user_idx=? and movie_idx = ?;";

        $st = $pdo->prepare($query);
        $st->execute([$user_idx, $movie_idx]);

        $query = "update MOVIE join ZZIM on MOVIE.movie_idx = ZZIM.movie_idx
                  set MOVIE.zzim = IF(status = 1, MOVIE.zzim + 1, MOVIE.zzim - 1)
                  where user_idx = ? and MOVIE.movie_idx = ?;";

        $st = $pdo->prepare($query);
        $st->execute([$user_idx, $movie_idx]);

        $query = "select ZZIM.user_idx, ZZIM.movie_idx, k_name,status
              from MOVIE, ZZIM
              where MOVIE.movie_idx = ZZIM.movie_idx and user_idx = ? and ZZIM.movie_idx = ?;";
        $st = $pdo->prepare($query);
        $st->execute([$user_idx, $movie_idx]);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $res = $st->fetchAll();
        $pdo->commit();

        $st = null;
        $pdo = null;

        return $res;
    } catch (Exception $exception){
        $pdo->rollback();
    }
}

// 극장 좋아요 누르기
function chgBranchLike($user_idx, $branch_idx){
    $pdo = pdoSqlConnect();
    try{
        $pdo->beginTransaction();
        $query = "update LIKE_BRANCH set status = if(status=1,0,1) where user_idx=? and branch_idx = ?;";

        $st = $pdo->prepare($query);
        $st->execute([$user_idx, $branch_idx]);

        $query = "select LIKE_BRANCH.user_idx, LIKE_BRANCH.branch_idx, branch_name,status
              from BRANCH, LIKE_BRANCH
              where BRANCH.branch_idx = LIKE_BRANCH.branch_idx and user_idx = ? and LIKE_BRANCH.branch_idx = ?;";
        $st = $pdo->prepare($query);
        $st->execute([$user_idx, $branch_idx]);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $res = $st->fetchAll();
        $pdo->commit();

        $st = null;
        $pdo = null;

        return $res;

    } catch (Exception $exception){
        $pdo->rollback();
    }
}
