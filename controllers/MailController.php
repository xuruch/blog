<?php 
namespace controllers;

use libs\Mail;

class MailController {

    public function send(){

        $redis = \libs\Redis::getRedis();
        
        $mailer = new Mail;
        // 设置用不超时
        ini_set('default_socket_timeout', -1);

        echo "发邮件队列启动成功..\r\n";

        while(true){
            $data = $redis->brpop('email',0);
            // var_dump($data);
            // die;
            $message = json_decode($data[1],true);
             // 2. 发邮件
            $mailer->send($message['title'], $message['content'], $message['from']);
            echo "发送邮件成功！继续等待下一个。\r\n";
        }

    }
}
