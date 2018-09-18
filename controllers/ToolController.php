<?php
namespace controllers;
use models\User;

class ToolController {


    public function __construct(){
        if(config('mode') != 'dev')
        {
            die('非法访问');
        }
    }

    public function users(){
        
        $user = new User;
        $data = $user->getAll();
        // var_dump($data);die;
        echo json_encode([
            'status_code' => 200,
            'data' => $data
        ]);
    }

    // 切换账号
    public function dologin(){
        $email = $_GET['email'];
        // 退出
        $_SESSION = [];
        // 重新登录
        $user = new \models\User;
        $user->dologin($email, md5('123'));
    }

}