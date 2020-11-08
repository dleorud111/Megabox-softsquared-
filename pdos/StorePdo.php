<?php

// 오늘의 메뉴 조회
function getTodayMenu()
{
    $pdo = pdoSqlConnect();
    $query = "select photo, name, description, concat((price div 1000),',000원') as price
              from STORE_MENU order by rand() limit 1;";

    $st = $pdo->prepare($query);
    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();
    $st = null;
    $pdo = null;

    return $res;
}

// 메가티켓 조회
function getMegaTicket()
{
    $pdo = pdoSqlConnect();
    $query = "select photo, name, description, concat((price div 1000),',000원') as price from MEGA_TICKET;";

    $st = $pdo->prepare($query);
    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();
    $st = null;
    $pdo = null;

    return $res;
}

// 팝콘, 음료, 굿즈 상품 조회
function getMenus()
{
    $pdo = pdoSqlConnect();
    $query = "select photo, name, description, concat((price div 1000),',000원') as price from STORE_MENU;";

    $st = $pdo->prepare($query);
    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();
    $st = null;
    $pdo = null;

    return $res;
}