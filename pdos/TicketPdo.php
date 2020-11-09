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

        $query = "select distinct k_name, grade,type, concat(theater_idx, '관') as theater_idx, concat(seat_num, '석') as total_seat, 
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
    $query = "select distinct k_name, grade,type, concat(theater_idx, '관') as theater_idx, concat(seat_num, '석') as total_seat, date,
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
}

// 영화별 예매(포스터 나열)
function getMovieTicketing(){
    $pdo = pdoSqlConnect();
    $query = "select poster, k_name, case when grade='전체관람가' then 'grade_all'
                            when grade='12세이상관람가' then 'grade_12'
                            when grade='15세이상관람가' then 'grade_15'
                            when grade='청소년관람불가' then 'grade_adult'
                            end as grade
              from MOVIE;";

    $st = $pdo->prepare($query);
    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();
    $st = null;
    $pdo = null;

    return $res;
}

// 좌석 선택
function putSeat($user_idx,$theater_info_idx,$seat){
    $pdo = pdoSqlConnect();
    try{
        $pdo->beginTransaction();
        $seat_split = explode(" ",$seat);

        foreach ($seat_split as $seat_type){
            $query = "update THEATER_SEAT set user_idx = ? where theater_info_idx = ? and seat_type = ?;";
            $st = $pdo->prepare($query);
            $st->execute([$user_idx, $theater_info_idx, $seat_type]);
        }
        $query1 = "insert into TICKET_CHECK (user_idx, theater_info_idx, all_price, total_price, created_at)
                   select user_idx, theater_info_idx, count(*)*10000 as all_price, count(*)*10000 as total_price, now()
                   from THEATER_SEAT where user_idx = ? and theater_info_idx = ?;";

        $query2 = "select concat(count(*)*10000 div 1000,',', '000원') as price
                  from THEATER_SEAT
                  where theater_info_idx = ? and user_idx = ?;";

        $st = $pdo->prepare($query1);
        $st->execute([$user_idx, $theater_info_idx]);

        $st = $pdo->prepare($query2);
        $st->execute([$theater_info_idx, $user_idx]);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $res= $st->fetchAll();

        $pdo->commit();
        $st = null;
        $pdo = null;

        return $res;

    } catch (Exception $exception){
        $pdo->rollback();
    }

}

// 좌석 선택시 중복 검사
function isValidSameSeat($theater_info_idx, $seat){
    $pdo = pdoSqlConnect();

        $seat_split = explode(" ",$seat);

        foreach ($seat_split as $seat_type){
            $query = "select exists(select theater_info_idx, seat_type 
                      from THEATER_SEAT 
                      where theater_info_idx = ? and seat_type = ? and user_idx is null) as exist;";
            $st = $pdo->prepare($query);
            $st->execute([$theater_info_idx,$seat_type]);
            $st->setFetchMode(PDO::FETCH_ASSOC);
            $res = $st->fetchAll();

            return intval($res[0]["exist"]);
        }
            $st = null;
            $pdo = null;
}

// 예매 확인 조회
function getTicket($user_idx){
    $pdo = pdoSqlConnect();
    $query = "select k_name, date_format(date, '%Y.%m.%d') as date,
                     case weekday(date) when 0 then '(월)' when 1 then '(화)' when 2 then '(수)'
                                        when 3 then '(목)' when 4 then '(금)' when 5 then '(금)'
                                        when 6 then '(토)' when 7 then '(일)' end as day,
                     concat(time_format(start_time, '%H:%i'),' ~ ', time_format(end_time, '%H:%i')) as runngin_time,
                     concat(branch_name,' ',theater_idx,'관') as theater,
                     concat('성인', count(*)) as person_num,
                     all_price , sale_price, total_price
              from TICKET_CHECK, THEATER_INFO, MOVIE, BRANCH, THEATER_SEAT
              where TICKET_CHECK.theater_info_idx = THEATER_INFO.theater_info_idx and BRANCH.branch_idx = THEATER_INFO.branch_idx and
                    THEATER_INFO.theater_info_idx = THEATER_SEAT.theater_info_idx and THEATER_INFO.movie_idx = MOVIE.movie_idx and
                    TICKET_CHECK.user_idx = THEATER_SEAT.user_idx and TICKET_CHECK.user_idx = ? and TICKET_CHECK.is_deleted = 0;";

    $st = $pdo->prepare($query);
    $st->execute([$user_idx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();
    $st = null;
    $pdo = null;

    return $res;
}

function request_curl($url, $is_post=0, $data=array(), $custom_header=null) {
    $ch = curl_init();
    curl_setopt ($ch, CURLOPT_URL,$url);
    curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt ($ch, CURLOPT_SSLVERSION,1);
    curl_setopt ($ch, CURLOPT_POST, $is_post);
    if($is_post) {
        curl_setopt ($ch, CURLOPT_POSTFIELDS, $data);
    }

    curl_setopt ($ch, CURLOPT_TIMEOUT, 300);
    curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt ($ch, CURLOPT_FOLLOWLOCATION, 1);
    //curl_setopt ($ch, CURLOPT_HEADER, true);

    if($custom_header) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $custom_header);
    }
    $result[0] = curl_exec ($ch);
    $result[1] = curl_errno($ch);
    $result[2] = curl_error($ch);
    $result[3] = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close ($ch);
    return $result;
}

// 결제 관련 정보가져오기(DB로 부터)
function getOrderId($user_idx)
{
    $pdo = pdoSqlConnect();
    $query = "select ticket_check_idx, sale_price, total_price
              from TICKET_CHECK
              where user_idx = ? and status = 0;";

    $st = $pdo->prepare($query);
    $st->execute([$user_idx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();
    $st = null;
    $pdo = null;

    return $res[0]['ticket_check_idx'];
}
function getSalePrice($user_idx)
{
    $pdo = pdoSqlConnect();
    $query = "select ticket_check_idx, sale_price, total_price
              from TICKET_CHECK
              where user_idx = ? and status = 0;";

    $st = $pdo->prepare($query);
    $st->execute([$user_idx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();
    $st = null;
    $pdo = null;

    return $res[0]['sale_price'];
}
function getTotalPrice($user_idx)
{
    $pdo = pdoSqlConnect();
    $query = "select ticket_check_idx, sale_price, total_price
              from TICKET_CHECK
              where user_idx = ? and status = 0;";

    $st = $pdo->prepare($query);
    $st->execute([$user_idx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();
    $st = null;
    $pdo = null;

    return $res[0]['total_price'];
}

// 티켓 결제 API(결제 창 호출)
function postPayment($user_idx,$order_id,$sale_price,$total_price)
{



    $http_host       = 'https://test.daekyung.shop';
    $adminkey       = 'c1f3a9937cd3d917472eec8940d1252f';       // admin 키
    $cid                = 'TC0ONETIME';                // cid

    $req_auth   = 'Authorization: KakaoAK '.$adminkey;
    $req_cont   = 'Content-type: application/x-www-form-urlencoded;charset=utf-8';

    $kakao_header = array( $req_auth, $req_cont );

    $approval_url   = $http_host."/CallPaymentKakaoPaySuccess";
    $cancel_url     = $http_host."/CallPaymentKakaoPayCancle";
    $fail_url          = $http_host."/CallPaymentKakaoPayCancle";

    $kakao_params = array(
        'cid'               => $cid,                                    // 가맹점코드 10자
        'partner_order_id'  => $order_id,                   // 주문번호
        'partner_user_id'   => $user_idx,                               // 유저 id
        'item_name'         => 'ticket',                                // 상품명
        'quantity'          => '1',                                     // 상품 수량
        'total_amount'      => $total_price,                // 상품 총액
        'tax_free_amount'   => $sale_price,                                     // 상품 비과세 금액
        'approval_url'      => $approval_url,                           // 결제성공시 콜백url 최대 255자
        'cancel_url'        => $cancel_url,
        'fail_url'          => $fail_url,
    );

    //pre($kakao_params);

    $strArrResult = request_curl('https://kapi.kakao.com/v1/payment/ready', 1, http_build_query($kakao_params), $kakao_header);

    //pre($strArrResult);

    if( $strArrResult[3] != '200' ) {
        echo "<script>";
        echo "alert('에러입니다. 관리자에게 문의하세요.');";
        echo "</script>";
        return;
    }

    $strArrResult = json_decode($strArrResult[0]);


    setcookie("user_id", $user_idx, time() + 60);
    setcookie("kakao_order_id", '5', time() + 60);
    setcookie("kakao_tid", $strArrResult->tid, time() + 60);

    echo "<script>";
    echo "var win = window.open('','','toolbar=no,location=no,status=no,menubar=no,scrollbars=yes,resizable=no,width=540,height=700,left=100,top=100');";
    echo "win.document.write('<iframe width=100%, height=650 src=".$strArrResult->next_redirect_pc_url." frameborder=0 allowfullscreen></iframe>')";
    echo "</script>";
}




// 결제 성공시 호출
function CallPaymentKakaoPaySuccess($user_idx, $pg_token)
{

    $adminkey = 'c1f3a9937cd3d917472eec8940d1252f';       // admin 키
    $cid = 'TC0ONETIME';                // cid


    $req_auth = 'Authorization: KakaoAK ' . $adminkey;
    $req_cont = 'Content-type: application/x-www-form-urlencoded;charset=utf-8';

    $kakao_header = array($req_auth, $req_cont);

    $kakao_params = array(
        'cid' => $cid,                            // 가맹점코드 10자
        'tid' => $_COOKIE['kakao_tid'],         // 결제 고유번호. 결제준비 API의 응답에서 얻을 수 있음
        'partner_order_id' => $_COOKIE['kakao_order_id'],    // 가맹점 주문번호. 결제준비 API에서 요청한 값과 일치해야 함
        'partner_user_id' => $_COOKIE['user_id'],           // 가맹점 회원 id. 결제준비 API에서 요청한 값과 일치해야 함
        'pg_token' => $pg_token    // 결제승인 요청을 인증하는 토큰. 사용자가 결제수단 선택 완료시 approval_url로 redirection해줄 때 pg_token을 query string으로 넘겨줌
        //'payload'           => ,                              // 해당 Request와 매핑해서 저장하고 싶은 값. 최대 200자
    );

    $strArrResult = request_curl('https://kapi.kakao.com/v1/payment/approve', 1, http_build_query($kakao_params), $kakao_header);

    $IS_PAYMENT_SUCCESS = false;

    if ($strArrResult[3] != '200') {
        echo "<script>";
        echo "alert('에러입니다. 관리자에게 문의하세요.');";
        echo "window.parent.close();";
        echo "</script>";
        return;
    }

    setcookie("user_id", '', time() - 3600);
    setcookie("kakao_order_id", '', time() - 3600);
    setcookie("kakao_tid", '$strArrResult->tid', time() - 3600);
//    $strArrResult = json_decode($strArrResult[0]);

    // LGD 로 쓰는 이유는 기존 table를 활용해서 같이쓰기위함.
//    $paymentResultArr = array(
//        'LGD_TID' => $strArrResult->tid,                     // kakao 거래 고유 번호
//        'LGD_MID' => $strArrResult->cid,                     // 상점아이디
//        'LGD_OID' => $strArrResult->partner_order_id,        // 상점주문번호
//        'LGD_AMOUNT' => $strArrResult->amount->total,           // 결제금액
//        'LGD_RESPCODE' => '0000',                                 // 결과코드
//        'LGD_RESPMSG' => '결제성공',                                       // 결과메세지
//
//        'LGD_FINANCENAME' => $strArrResult->card_info->purchase_corp,         // 은행명
//        'LGD_FINANCECODE' => $strArrResult->card_info->purchase_corp_code,    // 은행코드
//
//        'LGD_PAYTYPE' => $strArrResult->payment_method_type,              // 결제 방법 ( CARD, MONEY )
//
//        'LGD_PAYDATE' => $strArrResult->approved_at,                      // 승인시간 (모든 결제 수단 공통)
//        'LGD_FINANCEAUTHNUM' => $strArrResult->card_info->approved_id,           // 신용카드 승인번호
//        'LGD_CARDNOINTYN' => $strArrResult->card_info->interest_free_install, // 신용카드 무이자 여부 ( Y: 무이자,  N : 일반)
//        'LGD_CARDINSTALLMONTH' => $strArrResult->card_info->install_month,         // 신용카드 할부개월
//
//    );

    $pdo = pdoSqlConnect();
    $query = "update TICKET_CHECK set status = 1 where user_idx = ? and status = 0;";

    $st = $pdo->prepare($query);
    $st->execute([$user_idx]);

    $st = null;
    $pdo = null;



}