<?php 

// 常量
define('ROOT', dirname(__FILE__) . '/../');

// 引入 composer 自动加载文件
require(ROOT.'vendor/autoload.php');

// 类的自动加载
function autoload($class){

    $path = str_replace('\\', '/', $class);
    require(ROOT . $path . '.php');
}
spl_autoload_register('autoload');

if(php_sapi_name() == 'cli') {
    $controller = ucfirst($argv[1]) . 'Controller';
    $action = $argv[2];
}else {
    if( isset($_SERVER['PATH_INFO']) ){
        $pathInfo = $_SERVER['PATH_INFO'];
        // 转成数组
        $pathInfo = explode('/', $pathInfo);
    
        $controller = ucfirst($pathInfo[1]) . 'Controller';
        $action = $pathInfo[2];
    } else {
        // 默认控制器和方法
        $controller = 'IndexController';
        $action = 'index';
    }
}

// 为控制器添加命名空间
$fullController = 'controllers\\'.$controller;
$_C = new $fullController;
$_C->$action();

// 加载视图
function view($viewFileName, $data = []){
    extract($data);
    $path = str_replace('.', '/', $viewFileName) . '.html';
    // 加载视图
    require(ROOT . 'views/' . $path);
}

// 获取当前 URL 上所有的参数，并且还能排除掉某些参数
function getUrlParams($except = []){

    foreach($except as $v){
        unset($_GET[$v]);
    }
    $str = '';
    
    foreach($_GET as $k => $v){
        $str .= "$k=$v&";
    }
    return $str;

}

// 获取配置文件
function config($name){

    static $config = null;
    if($config == null){
        $config = require(ROOT.'config.php');
    }
    return $config[$name];
}