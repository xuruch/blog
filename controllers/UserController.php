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

        // 生成激活码
        $code = md5(rand(1,9999));
        // 保存到redis
        $redis = \libs\Redis::getRedis();  
        $value = json_encode([
            "email"=>$email,
            "pwd"=>$pwd
        ]);
        $key = "activation_user:{$code}";
        $redis->setex($key, 100, $value);


        // $user = new User;
        // $us = $user->add($email,$pwd);
        // // var_dump($us);
        // if(!$us){
        //     die("注册失败");
        // }

        // 从邮箱地址中取出姓名 
        $name = explode('@', $email);
        // 构造收件人地址[    fortheday @ 126.com   ,    fortheday  ]
        $from = [$email, $name[0]];
        // echo "<pre>";
        // var_dump($from);die;
        // 发邮件
        // 构造消息数组
        $message = [
            'title' => '治疗系统-账号激活',
            'content' => "点击以下链接进行激活：<br> <a href='http://locahost:9999/user/active_user?code={$code}'>
            http://localhost:9999/user/active_user?code={$code}</a><p>如果不能激活的话、就把上面这句话复制到浏览器打开</p>。",
            'from' => $from,
        ];
        $message = json_encode($message);

        $redis = \libs\Redis::getRedis(); 

        $redis->lpush('email', $message);
        echo '<script>alert("注册成功")</script>';
    }

    public function active_user(){

        $code = $_GET['code'];

        $redis = \libs\Redis::getRedis(); 

        $key = "activation_user:{$code}";

        $data = $redis->get($key);

        if($data){

            $redis->del($key);
            $value = json_decode($data,true);
            $user = new User;
            $user->add($value['email'],$value['pwd']);
            header('location:/user/login');
        }else {
            die("激活码无效");
        }
    }

}