<?php 
namespace controllers;
class TestController {

    function test(){
        $redis = config('redis');
        echo "<pre>";
        var_dump($redis);
    }

    public function blog(){
        $pdo = new PDO('mysql:host=127.0.0.1;dbname=blog', 'root', '198211');
        $pdo->exec('SET NAMES utf8');

        // 清空表，并且重置 ID
        $pdo->exec('TRUNCATE blogs');

        for($i=0;$i<300;$i++)
        {
            $title = $this->getChar( rand(20,100) ) ;
            $content = $this->getChar( rand(100,600) );
            $display = rand(10,500);
            $is_show = rand(0,1);
            $date = rand(1233333399,1535592288);
            $date = date('Y-m-d H:i:s', $date);
            $user_id = rand(1,20);
            $pdo->exec("INSERT INTO blogs (title,content,display,is_show,created_at,user_id) VALUES('$title','$content',$display,$is_show,'$date',$user_id)");
        }
    }

    public function users(){
        // 20个账号
        $pdo = new PDO('mysql:host=127.0.0.1;dbname=blog', 'root', '198211');
        $pdo->exec('SET NAMES utf8');

        // 清空表，并且重置 ID
        $pdo->exec('TRUNCATE users');

        for($i=0;$i<20;$i++)
        {
            $email = rand(50000,99999999999).'@126.com';
            $password = md5('123123');
            $pdo->exec("INSERT INTO users (email,password) VALUES('$email','$password')");
        }
    }

}