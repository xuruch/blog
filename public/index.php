<?php 
// 使用redis保存 SESSION
ini_set('session.save_handler', 'redis');
ini_set('session.save_path', 'tcp://127.0.0.1:6379?database=3');
ini_set('session.gc_maxlifetime', 1000000);
session_start();

// 如果用户以 POST 方式访问网站时，需要验证令牌
// if($_SERVER['REQUEST_METHOD'] == 'POST'){
//     if(!isset($_POST['_token'])){
//         die('违法操作、请不要作死1');
//     }
//     if($_POST['_token'] != $_SESSION['token']){
//         var_dump($_POST['_token'],$_SESSION['token']);
//         die('违法操作、请不要作死2');
//     }
// }

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
    // var_dump($str);die;
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

// 封装函数页面跳转
// 跳转到任意页面
function redirect($route){
    header("location:".$route);
    die;
}
// 返回上一个页面
function back(){
    redirect($_SERVER['HTTP_REFERER']);
}

// 提示消息函数
function message($message,$type,$url,$seconds = 5){
    if($type == 0){
        echo "<script>alert('{$message}');location.href='{$url}'</script>";
        exit;
    }else if($type == 1){
        view('common.success', [
            'message' => $message,
            'url' => $url,
            'seconds' => $seconds
        ]);
    }else if($type == 2){
        $_SESSION['_MESS_'] = $message;
        redirect($url);
    }
}
// 防止csrf 攻击手段、
function csrf(){
    if(!isset($_SESSION['token'])){
        $token = md5( rand(1,99999).microtime() );
        $_SESSION['token'] = $token;
    }
    return $token;
}
// 生成影藏令牌
function csrf_field(){
    $csrf = isset($_SESSION['token']) ? $_SESSION['token'] : csrf();
    ECHO "<input type='hidden' name='_token' value='{$csrf}'>";
}