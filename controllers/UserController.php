<?php 
namespace controllers;
use models\User;

// 引入模型类
class UserController {

    public function register(){

        // 加载视图
        view('users.add');
    }

    public function store(){
        $email = $_POST['email'];
        $pwd = md5($_POST['pwd']);

        $user = new User;
        $us = $user->add($email,$pwd);
        // var_dump($us);
        if(!$us){
            die("注册失败");
        }

        // 从邮箱地址中取出姓名 
        $name = explode('@', $email);
        // 构造收件人地址[    fortheday @ 126.com   ,    fortheday  ]
        $from = [$email, $name[0]];
        // echo "<pre>";
        // var_dump($from);die;
        // 发邮件
        // 构造消息数组
        $message = [
            'title' => '欢迎加入全栈1班',
            'content' => "点击以下链接进行激活：<br> <a href=''>点击激活</a>。",
            'from' => $from,
        ];

        $message = json_encode($message);

        $redis = new \Predis\Client([
            'scheme' => 'tcp',
            'host'   => '127.0.0.1',
            'port'   => 6379,
        ]); 

        $ss = $redis->lpush('email', $message);
        echo '发送成功';
    }

}