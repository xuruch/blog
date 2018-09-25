<?php 
namespace controllers;
use models\User;
use models\Order;
use Intervention\Image\ImageManagerStatic as Image;

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

    // 排行榜
    public function setActiveUser(){
        $user = new User;
        $user->setActiveUser();
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


    // 上传头像 页面显示
    public function avatar(){
        view('users.avatar');
    }
    // 头像上传
    public function addavatar(){
        // $uploadDir = ROOT.'public/uploads';
        // $data = date("Y-m-d");
        // if(!is_dir($uploadDir.'/'.$data)){
        //     mkdir($uploadDir.'/'.$data,0777);
        // }
        // $ext = strrchr($_FILES['image']['name'],'.');
        // $name = md5(time().rand(1,9999));
        // $fileName = $uploadDir.'/'.$data.'/'.$name.$ext;
        // move_uploaded_file($_FILES['image']['tmp_name'],$fileName);
        
        $upload = \libs\Uploader::make();
        $headImage = $upload->upload('image', 'avatar');

        // 裁切图片
        $image = Image::make(ROOT . 'public/uploads/'.$path);
        // 注意：Crop 参数必须是整数，所以需要转成整数：(int)
        $image->crop((int)$_POST['w'], (int)$_POST['h'], (int)$_POST['x'], (int)$_POST['y']);
        // 保存时覆盖原图
        $image->save(ROOT . 'public/uploads/'.$path);

        $user = new User;
        $user->setAvatar('/uploads/'.$headImage);
        @unlink( ROOT . 'public/'.$_SESSION['avatar'] );
        $_SESSION['avatar'] = '/uploads/'.$headImage;
        message("头像上传成功",2,'/');
    }

    // 批量上传 试图显示
    public function album(){
        view('users.album');
    }

    public function addablum(){
        $uploadDir = ROOT.'public/uploads/';

        $data = date("Ymd");
        if(!is_dir($uploadDir.'/'.$data)){
            mkdir($uploadDir.'/'.$data,0777);
        }

        foreach($_FILES['images']['name'] as $k => $v){
            // var_dump($k);die;
            $ext = strrchr($v,'.');
            $name = md5(time().rand(1,9999));
            $name = $name . $ext;
            move_uploaded_file($_FILES['images']['tmp_name'][$k
        ],$uploadDir.$data.'/'.$name);
            echo $uploadDir . $data .'/' . $name . '<hr>';
        }
    }


    // 上传大图视图
    public function bigimage(){
        view('users.bigimage');
    }
    // 上传大图片
    public function uploadbig(){
        $count = $_POST['count'];
        $i = $_POST['i'];
        $size = $_POST['size'];
        $name = 'big_img'.$_POST['img_name'];
        $img = $_FILES['img'];
        // var_dump($img)
        move_uploaded_file( $img['tmp_name'] , ROOT.'tmp/'.$i);

        $redis = \libs\Redis::getRedis();
        $uploadedCount = $redis->incr($name);
        if($uploadedCount == $count){
            echo "11111111111111111";
            $fp = fopen(ROOT.'public/uploads/big/'.$name.'.png', 'a');
            for($i=0;$i<$count;$i++){
                fwrite($fp,file_get_contents(ROOT.'tmp/'.$i));
                unlink(ROOT.'tmp/'.$i);
            }
            fclose($fp);
            $redis->del($name);
        }
    }


}