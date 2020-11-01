<?php

//DB 정보
function pdoSqlConnect()
{
    try {
        $DB_HOST = "dleorud123.cy8s34m8klcr.ap-northeast-2.rds.amazonaws.com";
        $DB_NAME = "megabox";
        $DB_USER = "dleorud123";
        $DB_PW = "dleorud1103";
        $pdo = new PDO("mysql:host=$DB_HOST;dbname=$DB_NAME", $DB_USER, $DB_PW);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch (\Exception $e) {
        echo $e->getMessage();
    }
}