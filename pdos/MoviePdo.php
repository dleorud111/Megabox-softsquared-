<?php

/* ****************************************** 영화 정보 관련 함수 ****************************************** */

// 영화 순위 나열
function getMovies()
{
    $pdo = pdoSqlConnect();
    $query = "select @rank := @rank + 1 as ranking, movie_idx, poster,
                        case when grade='전체관람가' then 'grade_all'
                        when grade='12세이상관람가' then 'grade_12'
                        when grade='15세이상관람가' then 'grade_15'
                        when grade='청소년관람불가' then 'grade_adult'
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

// 영화 실관람평 조회
function getMovieReview($movie_idx){
    $pdo = pdoSqlConnect();

    $query = "select k_name, star from MOVIE where movie_idx = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$movie_idx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res['movie_info'] = $st->fetchAll();

    $query = "select concat(count(*),'건') as review_num from REVIEW where movie_idx = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$movie_idx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res['review_num'] = $st->fetchAll();

    $query = "select concat(substring(id, 1,length(id)-2), '**') as id, 
                         case when timestampdiff(second, REVIEW.created_at, now()) < 60
                              then concat(timestampdiff(second, REVIEW.created_at, now()),' 초전')
                              when timestampdiff(minute, REVIEW.created_at, now()) < 60
                              then concat(timestampdiff(minute, REVIEW.created_at, now()),' 분전')
                              when timestampdiff(hour, REVIEW.created_at, now()) < 24
                              then concat(timestampdiff(hour, REVIEW.created_at, now()),' 시간전')
                              when timestampdiff(day, REVIEW.created_at, now()) < 30
                              then concat(timestampdiff(day, REVIEW.created_at, now()),' 일전') end as time,
                    comment, star, like_num
              from REVIEW, USER
              where USER.idx = REVIEW.user_idx and movie_idx = ?
              order by REVIEW.created_at desc;";

    $st = $pdo->prepare($query);
    $st->execute([$movie_idx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res['review_list'] = $st->fetchAll();
    $st = null;
    $pdo = null;

    return $res;
}

// 실관람평 유효성 검사(봤는지)
function isValidPostReviewWatched($movie_idx, $user_idx)
{
    $pdo = pdoSqlConnect();
    $query = "select exists(select user_idx from TICKET_CHECK, THEATER_INFO
              where TICKET_CHECK.theater_info_idx = THEATER_INFO.theater_info_idx and movie_idx = ? and user_idx = ?
                    and TICKET_CHECK.updated_at is not null) as exist;";

    $st = $pdo->prepare($query);
    $st->execute([$movie_idx, $user_idx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();
    $st = null;
    $pdo = null;

    return intval($res[0]["exist"]);
}

// 실관람평 유효성 검사(이미 썼는지)
function isValidPostReviewDone($movie_idx, $user_idx)
{
    $pdo = pdoSqlConnect();
    $query = "select exists(select user_idx, movie_idx from REVIEW where movie_idx = ? and user_idx = ?) as exist;";

    $st = $pdo->prepare($query);
    $st->execute([$movie_idx, $user_idx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();
    $st = null;
    $pdo = null;

    return intval($res[0]["exist"]);
}

// 영화 실관람평 쓰기
function postMovieReview($movie_idx, $user_idx, $star, $comment){
    $pdo = pdoSqlConnect();
    $query = "insert into REVIEW(movie_idx, user_idx, star, comment, created_at) values (?,?,?,?,now());";

    $st = $pdo->prepare($query);
    $st->execute([$movie_idx, $user_idx, $star, $comment]);

    $st = null;
    $pdo = null;

}

// 영화 보고싶어 누르기
function chgReviewLike($user_idx, $review_idx){
    $pdo = pdoSqlConnect();
    try{
        $pdo->beginTransaction();

        $query = "update REVIEW_LIKE set status = if(status=1,0,1) where user_idx = ? and review_idx = ?;";

        $st = $pdo->prepare($query);
        $st->execute([$user_idx, $review_idx]);

        $query = "update REVIEW join REVIEW_LIKE on REVIEW.review_idx = REVIEW_LIKE.review_idx
                  set REVIEW.like_num = IF(status = 1, REVIEW.like_num + 1, REVIEW.like_num - 1)
                  where REVIEW_LIKE.user_idx = ? and REVIEW.review_idx = ?;";

        $st = $pdo->prepare($query);
        $st->execute([$user_idx, $review_idx]);

        $query = "select review_idx, user_idx, status
                  from REVIEW_LIKE
                  where review_idx = ? and user_idx = ?;";
        $st = $pdo->prepare($query);
        $st->execute([$review_idx, $user_idx]);
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

// 영화 무비 포스트 전체 조회
function getMoviePost($movie_idx){
    $pdo = pdoSqlConnect();

    $query = "select k_name from MOVIE where movie_idx = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$movie_idx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res['movie_info'] = $st->fetchAll();

    $query = "select MOVIE_POST.photo, concat(substring(id, 1,length(id)-2), '**') as id, content, like_num, comment_num
              from MOVIE_POST, USER, MOVIE
              where MOVIE_POST.user_idx = USER.idx and MOVIE.movie_idx = MOVIE_POST.movie_idx and MOVIE.movie_idx = ?";

    $st = $pdo->prepare($query);
    $st->execute([$movie_idx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res['movie_post'] = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

// 영화 무비 포스트 상세 조회
function getMoviePostDetail($movie_post_idx, $movie_idx){
    $pdo = pdoSqlConnect();

    $query = "select k_name, MOVIE_POST.photo, concat(substring(id, 1,length(id)-2), '**') as id, 
                     MOVIE_POST.created_at, content, like_num, comment_num
              from MOVIE_POST, USER, MOVIE
              where MOVIE_POST.user_idx = USER.idx and MOVIE.movie_idx = MOVIE_POST.movie_idx and 
                    movie_post_idx = ? and MOVIE_POST.movie_idx = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$movie_post_idx, $movie_idx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res['movie_post'] = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

// 무비 포스트 유효성 검사 (이미 썼는지)
function isValidMoviePostDone($movie_idx, $user_idx)
{
    $pdo = pdoSqlConnect();
    $query = "select exists(select user_idx, movie_idx from MOVIE_POST where movie_idx = ? and user_idx = ?) as exist;";

    $st = $pdo->prepare($query);
    $st->execute([$movie_idx, $user_idx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();
    $st = null;
    $pdo = null;

    return intval($res[0]["exist"]);
}

// 무비 포스트 쓰기
function postMoviePost($movie_idx, $user_idx, $photo, $content){
    $pdo = pdoSqlConnect();
    $query = "insert into MOVIE_POST(movie_idx, user_idx, photo, content, created_at) values(?,?,?,?,now());";

    $st = $pdo->prepare($query);
    $st->execute([$movie_idx, $user_idx, $photo, $content]);

    $st = null;
    $pdo = null;

}

// 무비포스트 존재 확인
function isValidMoviePost($movie_idx){
    $pdo = pdoSqlConnect();
    $query = "select exists(select movie_post_idx from MOVIE_POST where movie_post_idx = ?) as exist;";

    $st = $pdo->prepare($query);
    $st->execute([$movie_idx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();
    $st = null;
    $pdo = null;

    return intval($res[0]["exist"]);
}

// 무비 포스트 댓글 쓰기 유효성 검사(내 게시글인지 확인)
function isValidMoviePostMine($movie_post_idx, $user_idx)
{
    $pdo = pdoSqlConnect();
    $query = "select exists(select movie_post_idx, user_idx from MOVIE_POST where movie_post_idx=? and user_idx=?) as exist;";

    $st = $pdo->prepare($query);
    $st->execute([$movie_post_idx, $user_idx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();
    $st = null;
    $pdo = null;

    return intval($res[0]["exist"]);
}

// 무비 포스트 댓글 쓰기
function postMoviePostComment($movie_post_idx, $user_idx, $comment){
    $pdo = pdoSqlConnect();
    try{
        $pdo->beginTransaction();
        $query = "insert into MOVIE_POST_COMMENT(movie_post_idx, user_idx, comment, created_at) values (?,?,?,now());";

        $st = $pdo->prepare($query);
        $st->execute([$movie_post_idx, $user_idx, $comment]);

        $query = "update MOVIE_POST set comment_num = (select count(*) 
                                                       from MOVIE_POST_COMMENT 
                                                       where movie_post_idx = ?) 
                  where MOVIE_POST.movie_post_idx=?;";

        $st = $pdo->prepare($query);
        $st->execute([$movie_post_idx, $movie_post_idx]);

        $pdo->commit();
        $st = null;
        $pdo = null;

    } catch (Exception $exception){
        $pdo->rollback();
    }
}

// 무비 포스트 좋아요
function chgMoviePostLike($movie_post_idx, $user_idx)
{
    $pdo = pdoSqlConnect();
    try{
        $pdo->beginTransaction();
        $query = "update MOVIE_POST_LIKE set status = if(status=1,0,1) where user_idx=? and movie_post_idx = ?;";

        $st = $pdo->prepare($query);
        $st->execute([$user_idx, $movie_post_idx]);

        $query = "update MOVIE_POST set like_num = (select count(*) from MOVIE_POST_LIKE 
                                                    where movie_post_idx = ? and status = 1)
                  where MOVIE_POST.movie_post_idx=?;";

        $st = $pdo->prepare($query);
        $st->execute([$movie_post_idx, $movie_post_idx]);

        $query = "select movie_post_idx, user_idx, status from MOVIE_POST_LIKE where movie_post_idx=? and user_idx=?;";

        $st = $pdo->prepare($query);
        $st->execute([$movie_post_idx, $user_idx]);
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



