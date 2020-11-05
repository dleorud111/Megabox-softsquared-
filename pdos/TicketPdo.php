<?php

/* ****************************************** 영화 예매 관련 함수 ****************************************** */
// 지점 존재 유무 확인
function isValidBranch($branch_idx){
    $pdo = pdoSqlConnect();
    $query = "select exists(select branch_idx from BRANCH where branch_idx = ?) as exist;";

    $st = $pdo->prepare($query);
    $st->execute([$branch_idx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();
    $st = null;
    $pdo = null;

    return intval($res[0]["exist"]);
}

// 바로 예매(지점 조회)
function getBranchDirectTicketing($movie_idx){
    $pdo = pdoSqlConnect();
    try{
        $pdo->beginTransaction();
        $query = "with BRANCH_COUNT as (select branch_region, branch_name from THEATER_INFO, BRANCH
                                    where BRANCH.branch_idx = THEATER_INFO.branch_idx and movie_idx = ?
                                    group by branch_region, branch_name)
              select branch_region, count(branch_region) as count
              from BRANCH_COUNT
              group by branch_region;";

        $st = $pdo->prepare($query);
        $st->execute([$movie_idx]);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $res['branch_region'] = $st->fetchAll();

        $query = "select distinct BRANCH.branch_idx, branch_region, branch_name
              from BRANCH,THEATER_INFO
              where BRANCH.branch_idx = THEATER_INFO.branch_idx and movie_idx = ? ;";

        $st = $pdo->prepare($query);
        $st->execute([$movie_idx]);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $res['branch_name'] = $st->fetchAll();
        $pdo->commit();

        $st = null;
        $pdo = null;

        return $res;
    } catch (Exception $exception){
        $pdo->rollback();
    }

}

// 바로 예매(관, 시간 조회)
function getTheaterDirectTicketing($movie_idx, $branch_idx){
    $pdo = pdoSqlConnect();
    try{
        $pdo->beginTransaction();
        $query = "select branch_name from BRANCH where branch_idx = ?;";

        $st = $pdo->prepare($query);
        $st->execute([$branch_idx]);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $res['branch'] = $st->fetchAll();

        $query = "select distinct k_name, type, concat(theater_idx, '관') as theater_idx, concat(seat_num, '석') as total_seat, 
                              date, time_format(start_time, '%H:%i') as start_time, 
                              concat('~', time_format(end_time, '%H:%i')) as end_time, concat(count(*), '석') as rest_seat
              from BRANCH,THEATER_INFO, THEATER_SEAT, MOVIE
              where BRANCH.branch_idx = THEATER_INFO.branch_idx and THEATER_INFO.movie_idx = MOVIE.movie_idx and
                    THEATER_INFO.theater_info_idx = THEATER_SEAT.theater_info_idx and user_idx is null and
                    BRANCH.branch_idx = ? and THEATER_INFO.movie_idx = ?
              group by start_time;";

        $st = $pdo->prepare($query);
        $st->execute([$branch_idx, $movie_idx]);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $res['detail'] = $st->fetchAll();
        $pdo->commit();

        $st = null;
        $pdo = null;

        return $res;
    } catch (Exception $exception){
        $pdo->rollback();
    }

}

// 극장별 예매(지점 조회)
function getBranchTicketing(){
    $pdo = pdoSqlConnect();
    $query = "with BRANCH_COUNT as (select BRANCH.branch_region, count(*) as branch_count from BRANCH group by branch_region)
              select branch_idx, BRANCH.branch_region, branch_count,branch_name
              from BRANCH, BRANCH_COUNT
              where BRANCH.branch_region = BRANCH_COUNT.branch_region;";

    $st = $pdo->prepare($query);
    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();
    $st = null;
    $pdo = null;

    return $res;
}

// 극장별 예매(관,시간 조회)
function getTheaterBranchTicketing($branch_idx){
    $pdo = pdoSqlConnect();
    $query = "select distinct k_name, type, concat(theater_idx, '관') as theater_idx, concat(seat_num, '석') as total_seat, date,
                     time_format(start_time, '%H:%i') as start_time,
                     concat('~', time_format(end_time, '%H:%i')) as end_time, concat(count(*), '석') as rest_seat
              from BRANCH,THEATER_INFO, THEATER_SEAT, MOVIE
              where BRANCH.branch_idx = THEATER_INFO.branch_idx and THEATER_INFO.movie_idx = MOVIE.movie_idx and
                    THEATER_INFO.theater_info_idx = THEATER_SEAT.theater_info_idx and user_idx is null and
                    BRANCH.branch_idx = ?
              group by theater_idx, start_time;";

    $st = $pdo->prepare($query);
    $st->execute([$branch_idx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();
    $st = null;
    $pdo = null;

    return $res;
}

// 영화관 정보 유효성 검사
// 지점 존재 유무 확인
function isValidTheaterInfo($theater_info_idx){
    $pdo = pdoSqlConnect();
    $query = "select exists(select theater_info_idx from THEATER_INFO where theater_info_idx = ?) as exist;";

    $st = $pdo->prepare($query);
    $st->execute([$theater_info_idx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();
    $st = null;
    $pdo = null;

    return intval($res[0]["exist"]);
}

// 극장별 예매(관,시간 조회)
function getRestSeat($theater_info_idx){
    $pdo = pdoSqlConnect();
    try{
        $query = "select concat(theater_idx,'관') as theater, concat('(', time_format(start_time, '%H:%i'),')') as start_time
                  from THEATER_INFO where THEATER_INFO.theater_info_idx = ?;";

        $st = $pdo->prepare($query);
        $st->execute([$theater_info_idx]);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $res['theater_info'] = $st->fetchAll();

        $query = "select seat_type
              from THEATER_INFO, THEATER_SEAT
              where THEATER_INFO.theater_info_idx = THEATER_SEAT.theater_info_idx and
                    THEATER_INFO.theater_info_idx = ? and user_idx is null;";

        $st = $pdo->prepare($query);
        $st->execute([$theater_info_idx]);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $res['rest_seat'] = $st->fetchAll();
        $st = null;
        $pdo = null;

        return $res;
    } catch (Exception $exception){
        $pdo->rollback();
    }


}