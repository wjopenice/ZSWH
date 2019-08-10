<?php
use Yaf\Application;
use Yaf\Exception;
session_start();
define("APP_PATH",  realpath(dirname(__FILE__) . '/')); /* 指向public的上一级 */
define('ROOT_PATH', str_replace('\\', '/', dirname(__DIR__)) . '/');
define('UC_AUTH_KEY', 'oq0d^*AcXB$-2[]PkFaKY}eR(Hv+<?g~CImW>xyV');
define('ZSWH','http://www.zhishengwh.com');
define('WZAPP','http://app.zhishengwh.com');
//error_reporting(0);
$app  = new Application(APP_PATH . "/conf/application.ini",ini_get('yaf.environ'));
$app->bootstrap()->run();





