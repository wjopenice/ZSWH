<?php
use Phalcon\Loader;
use Phalcon\Mvc\View;
use Phalcon\Mvc\Application;
use Phalcon\Di\FactoryDefault;
use Phalcon\Mvc\Url as UrlProvider;
use Phalcon\Db\Adapter\Pdo\Mysql as DbAdapter;
session_start();
//error_reporting(0);
//【loader config file】
// Define some absolute path constants to aid in locating resources
define("BASE_PATH",dirname(__DIR__));
define("APP_PATH",BASE_PATH."/app");
require APP_PATH.'/common/functions.php';
// Register an autoloader
$loader = new Loader();
$loader->registerDirs([
    APP_PATH.'/controllers/',
    APP_PATH.'/models/',
]);
$loader->register();
// Create a DI
$di = new FactoryDefault();
// Setup the view component
$di->set('view',function (){
    $view = new View();
    $view->setViewsDir(APP_PATH.'/views/');
    //注册模板引擎
    $view->registerEngines([
        ".phtml" => \Phalcon\Mvc\View\Engine\Php::class,
        '.volt' => Phalcon\Mvc\View\Engine\Volt::class
    ]);
    return $view;
});
// Setup a base URI
$di->set('url',function (){
    $url = new UrlProvider();
    $url->setBaseUri('/');
    return $url;
});

// Setup the database service
$di->set('db',function (){
    return new DbAdapter([
        'host'     => '120.78.136.67',
        'username' => 'root',
        'password' => '12345678',
        'dbname'   => 'pettap',
        'port'     => '3306',
        'charset'  => 'utf8',
    ]);
});

$application = new Application($di);
try{
    // Handle the request
    $response = $application->handle($_SERVER["REQUEST_URI"]);
    $response->send();
}catch (\Exception $e){
    echo 'Exception:',$e->getMessage();
}



