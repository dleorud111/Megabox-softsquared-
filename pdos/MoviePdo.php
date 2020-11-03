<?php

//// 영화 순위 나열
//function getMovies()
//{
//    $pdo = pdoSqlConnect();
//    $query = "select @rank := @rank + 1 as ranking, movie_idx, poster,
//                        case when grade='전체관람가' then 'ALL'
//                        when grade='12세이상관람가' then '12'
//                        when grade='15세이상관람가' then '15'
//                        when grade='청소년관람불가' then '청불'
//                        end as grade,
//                    k_name, reservation,
//                        case when datediff(start_day, curdate()) > 0 then concat('D-',datediff(start_day, curdate()))
//                        when datediff(start_day, curdate()) < 0 then null
//                        end as start_day,
//                        case when datediff(start_day, curdate()) < 0 then star
//                        when datediff(start_day, curdate()) > 0 then null
//                        end as star,
//                    zzim
//             from MOVIE, (SELECT @RANK := 0) r
//             order by reservation desc;";
//
//    $st = $pdo->prepare($query);
//    $st->execute();
//    $res = $st->fetchAll(PDO::FETCH_ASSOC);
//    $st = null;
//    $pdo = null;
//
//    return $res;
//}
//
//// 영화 순위 나열
//function getHashTagMovies($hash_tag)
//{
//    $pdo = pdoSqlConnect();
//    $query = "select @rank := @rank + 1 as ranking, movie_idx, poster,
//                        case when grade='전체관람가' then 'ALL'
//                        when grade='12세이상관람가' then '12'
//                        when grade='15세이상관람가' then '15'
//                        when grade='청소년관람불가' then '청불'
//                        end as grade,
//                    k_name, reservation,
//                        case when datediff(start_day, curdate()) > 0 then concat('D-',datediff(start_day, curdate()))
//                        when datediff(start_day, curdate()) < 0 then null
//                        end as start_day,
//                        case when datediff(start_day, curdate()) < 0 then star
//                        when datediff(start_day, curdate()) > 0 then null
//                        end as star,
//                    zzim
//             from MOVIE, (SELECT @RANK := 0) r
//             where hash_tag regexp ?
//             order by reservation desc;";
//
//    $st = $pdo->prepare($query);
//    $st->execute([$hash_tag]);
//    $res = $st->fetchAll(PDO::FETCH_ASSOC);
//    $st = null;
//    $pdo = null;
//
//    return $res;
//}