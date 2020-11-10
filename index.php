<?php

require './pdos/DatabasePdo.php';
require './pdos/IndexPdo.php';
require './pdos/JWTPdo.php';
require './pdos/MoviePdo.php';
require './pdos/TicketPdo.php';
require './pdos/StorePdo.php';
require './vendor/autoload.php';

use \Monolog\Logger as Logger;
use Monolog\Handler\StreamHandler;

date_default_timezone_set('Asia/Seoul');
ini_set('default_charset', 'utf8mb4');

//에러출력하게 하는 코드
//error_reporting(E_ALL); ini_set("display_errors", 1);

//Main Server API
$dispatcher = FastRoute\simpleDispatcher(function (FastRoute\RouteCollector $r) {
    /* ******************   JWT   ****************** */
    $r->addRoute('POST', '/jwt', ['JWTController', 'createJwt']);   // JWT 생성: 로그인 + 해싱된 패스워드 검증 내용 추가
    $r->addRoute('GET', '/jwt', ['JWTController', 'validateJwt']);  // JWT 유효성 검사

    /* ******************   Test   ****************** */
    $r->addRoute('GET', '/', ['IndexController', 'index']);
    $r->addRoute('GET', '/users', ['IndexController', 'getUsers']);
    $r->addRoute('GET', '/users/{userIdx}', ['IndexController', 'getUserDetail']);
    //$r->addRoute('POST', '/user', ['IndexController', 'createUser']); // 비밀번호 해싱 예시 추가




    /* ******************************** 회원가입 및 로그인 ******************************** */

    $r->addRoute('POST', '/login', ['IndexController', 'login']); //소셜 로그인 API

    $r->addRoute('POST', '/user', ['IndexController', 'postUser']); //회원가입 API


    /* ******************************** 영화 정보 관련 기능 ******************************** */

    $r->addRoute('GET', '/movie', ['MovieController', 'getMovies']); //메인화면 영화 순위 나열 API

    $r->addRoute('GET', '/movie/{movie_idx}/movie-intro', ['MovieController', 'getMovieIntro']); //영화 간단 소개(포스터 터치 후 위) API

    $r->addRoute('GET', '/movie/{movie_idx}/movie-info', ['MovieController', 'getMovieInfo']); //영화 상세 정보(포스터 터치 후 아래) API

    $r->addRoute('GET', '/movie/{movie_idx}/review', ['MovieController', 'getMovieReview']); //영화 실관람평 조회 API

    $r->addRoute('POST', '/movie/{movie_idx}/review', ['MovieController', 'postMovieReview']); //영화 실관람평 쓰기 API

    $r->addRoute('PATCH', '/review/{review_idx}/like', ['MovieController', 'chgReviewLike']); // 실관람평에 좋아요 API

    $r->addRoute('PATCH', '/movie/{movie_idx}/like', ['MovieController', 'chgMovieHeart']); //영화 보고싶어(찜하기) API

    $r->addRoute('PATCH', '/branch/{branch_idx}/like', ['MovieController', 'chgBranchLike']); //극장 좋아요 API

    $r->addRoute('GET', '/movie/{movie_idx}/movie-post', ['MovieController', 'getMoviePost']); //무비포스트 전체 조회 API

    $r->addRoute('GET', '/movie/{movie_idx}/movie-post/{movie_post_idx}', ['MovieController', 'getMoviePostDetail']); //무비포스트 상세 조회 API

    $r->addRoute('POST', '/movie/{movie_idx}/movie-post', ['MovieController', 'postMoviePost']); //무비포스트 쓰기 API

    $r->addRoute('POST', '/movie-post/{movie_post_idx}/comment', ['MovieController', 'postMoviePostComment']); //무비포스트 댓글 쓰기 API

    $r->addRoute('PATCH', '/movie-post/{movie_post_idx}/like', ['MovieController', 'chgMoviePostLike']); //무비포스트에 좋아요 API

    /* ******************************** 영화 예매 관련 기능 ******************************** */

    $r->addRoute('GET', '/movie/{movie_idx}/direct-ticketing', ['TicketController', 'getBranchDirectTicketing']); //바로 예매(지점 조회) API

    $r->addRoute('GET', '/movie/{movie_idx}/branch_idx/{branch_idx}/direct-ticketing', ['TicketController', 'getTheaterDirectTicketing']); //바로 예매(관,시간 조회) API

    $r->addRoute('GET', '/branch-ticketing', ['TicketController', 'getBranchTicketing']); //극장별 예매(지점 조회) API

    $r->addRoute('GET', '/branch_idx/{branch_idx}/branch-ticketing', ['TicketController', 'getTheaterBranchTicketing']); //극장별 예매(관,시간 조회) API

    $r->addRoute('GET', '/movie-ticketing', ['TicketController', 'getMovieTicketing']); //영화별 예매(포스터 나열) API

    $r->addRoute('GET', '/theater_info_idx/{theater_info_idx}/movie-seat', ['TicketController', 'getRestSeat']); //해당 영화관 남은 좌석 조회 API

    $r->addRoute('POST', '/movie-seat/selecting', ['TicketController', 'putSeat']); //좌석선택 API

    $r->addRoute('GET', '/ticketing/check', ['TicketController', 'getTicket']); //예매확인 API

    $r->addRoute('POST', '/payment', ['TicketController', 'postPayment']); //티켓 결제창(확인용 web)(카카오페이) API

    $r->addRoute('GET', '/payment/success', ['TicketController', 'getPayment']); //티켓 결제 성공 API(카카오페이) API

    $r->addRoute('GET', '/ticket', ['TicketController', 'getTicketPayed']); //예매/주문내역 API

    $r->addRoute('GET', '/mobile-ticket', ['TicketController', 'getMobileTicket']); //모바일티켓 조회 API

    /* ******************************** 스토어 관련 기능 ******************************** */

    $r->addRoute('GET', '/store', ['StoreController', 'getTodayMenu']); //오늘의 메뉴 조회(스토어 상단) API

    $r->addRoute('GET', '/store/mega-ticket', ['StoreController', 'getMegaTicket']); //메가티켓 조회(스토어 하단) API

    $r->addRoute('GET', '/store/popcorn-drink-goods', ['StoreController', 'getMenus']); //팝콘, 음료, 굿즈 상품 조회(스토어 하단) API



    /* ******************************** client 요구 API ******************************** */
    $r->addRoute('GET', '/branch/{branch}/theater/{theater}/date/{date}/time/{time}', ['IndexController', 'getTheaterInfoIdx']);


//    $r->addRoute('GET', '/users', 'get_all_users_handler');
//    // {id} must be a number (\d+)
//    $r->addRoute('GET', '/user/{id:\d+}', 'get_user_handler');
//    // The /{title} suffix is optional
//    $r->addRoute('GET', '/articles/{id:\d+}[/{title}]', 'get_article_handler');
});

// Fetch method and URI from somewhere
$httpMethod = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

// Strip query string (?foo=bar) and decode URI
if (false !== $pos = strpos($uri, '?')) {
    $uri = substr($uri, 0, $pos);
}
$uri = rawurldecode($uri);

$routeInfo = $dispatcher->dispatch($httpMethod, $uri);

// 로거 채널 생성
$accessLogs = new Logger('ACCESS_LOGS');
$errorLogs = new Logger('ERROR_LOGS');
// log/your.log 파일에 로그 생성. 로그 레벨은 Info
$accessLogs->pushHandler(new StreamHandler('logs/access.log', Logger::INFO));
$errorLogs->pushHandler(new StreamHandler('logs/errors.log', Logger::ERROR));
// add records to the log
//$log->addInfo('Info log');
// Debug 는 Info 레벨보다 낮으므로 아래 로그는 출력되지 않음
//$log->addDebug('Debug log');
//$log->addError('Error log');

switch ($routeInfo[0]) {
    case FastRoute\Dispatcher::NOT_FOUND:
        // ... 404 Not Found
        echo "404 Not Found";
        break;
    case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
        $allowedMethods = $routeInfo[1];
        // ... 405 Method Not Allowed
        echo "405 Method Not Allowed";
        break;
    case FastRoute\Dispatcher::FOUND:
        $handler = $routeInfo[1];
        $vars = $routeInfo[2];

        switch ($routeInfo[1][0]) {
            case 'IndexController':
                $handler = $routeInfo[1][1];
                $vars = $routeInfo[2];
                require './controllers/IndexController.php';
                break;
            case 'JWTController':
                $handler = $routeInfo[1][1];
                $vars = $routeInfo[2];
                require './controllers/JWTController.php';
                break;
            case 'MovieController':
                $handler = $routeInfo[1][1]; $vars = $routeInfo[2];
                require './controllers/MovieController.php';
                break;
            case 'TicketController':
                $handler = $routeInfo[1][1]; $vars = $routeInfo[2];
                require './controllers/TicketController.php';
                break;
            case 'StoreController':
                $handler = $routeInfo[1][1]; $vars = $routeInfo[2];
                require './controllers/StoreController.php';
                break;
            /*case 'ProductController':
                $handler = $routeInfo[1][1]; $vars = $routeInfo[2];
                require './controllers/ProductController.php';
                break;
            case 'SearchController':
                $handler = $routeInfo[1][1]; $vars = $routeInfo[2];
                require './controllers/SearchController.php';
                break;
            case 'ReviewController':
                $handler = $routeInfo[1][1]; $vars = $routeInfo[2];
                require './controllers/ReviewController.php';
                break;
            case 'ElementController':
                $handler = $routeInfo[1][1]; $vars = $routeInfo[2];
                require './controllers/ElementController.php';
                break;
            case 'AskFAQController':
                $handler = $routeInfo[1][1]; $vars = $routeInfo[2];
                require './controllers/AskFAQController.php';
                break;*/
        }

        break;
}
