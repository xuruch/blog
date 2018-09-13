<?php 
namespace controllers;
use models\User;
use models\Order;

// 引入模型类
class UserController {

    // 注册
    public function register(){
        // 加载视图
        view('users.add');
    }

    // 登陆
    public function login(){
        view('users.login');
    }
    public function dologin(){
        $email = $_POST['email'];
        $pwd = md5($_POST['pwd']);

        $user = new User;
        $u = $user->dologin($email,$pwd);
        // var_dump($u);
        if($u){
            message('登陆成功',2,'/blog/index');
        }else {
            message('登陆失败',1,'/user/login');
        }
    }

    // 退出
    public function loginout(){
        $_SESSION = [];
        redirect('/');
    }
    // 显示余额
    public function money(){
        $user = new User;
        echo $user->getMoney();
    }

    // 显示充值页面
    public function charge(){
        view('users.charge');
    }
    // 充值
    public function docharge(){

        $money = $_POST['money'];
        // var_dump($money);die;   
        $create = new Order;
        $create->create($money);
        message('充值订单已生成，请立即支付',2,'/user/orders');
    }

    // 查询微信订单 
    public function orderStatus(){
        $sn = $_GET['sn'];

        $model = new Order;
        $info = $model->findBySn($sn);

        echo $info['status'];
    }


    // 列出所有的订单
    public function orders(){
        
        $order = new Order;
        $data = $order->search();
        view('users.orders',$data);
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

    // 激活账号
    public function active_user(){

        $code = $_GET['code'];

        $redis = \libs\Redis::getRedis(); 

        $key = "activation_user:{$code}";

        $data = $redis->get($key);

        if($data){

            $redis->del($key);
            $value = json_decode($data,true);
            $user = new User;
            $user->addUser($value['email'],$value['pwd']);
            header('location:/user/login');
        }else {
            die("激活码无效");
        }
    }

}