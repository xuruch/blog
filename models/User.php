<?php 
namespace models;
use PDO;
class User extends Base {
        
    // 添加用户名
    public function addUser($email,$pwd){
        $stmt = self::$pdo->prepare("INSERT INTO users(email,password) VALUES(?,?)");
        return  $stmt->execute([
                    $email,
                    $pwd
                ]);
    }

    // 登陆用户名
    public function dologin($email,$pwd){

        $stmt = self::$pdo->prepare("SELECT * FROM users where email=? and password=?");
        $stmt->execute([
                $email,
                $pwd
        ]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if($user){
            $_SESSION['id'] = $user['id'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['money'] = $user['money'];
            $_SESSION['avatar'] = $user['avatar'];

            return TRUE;
        } else {
            return FALSE;
        }
    }

    // 为用户增加金额
    public function addMoney($money, $userId) {
        $stmt = self::$pdo->prepare("UPDATE users SET money=money+? WHERE id=?");
        return  $stmt->execute([
                    $money,
                    $userId
                ]);
    }
    // 获取余额显示
    public function getMoney(){
        $id = $_SESSION['id'];
        $stmt = self::$pdo->prepare('SELECT money from users where id = ?');
        $stmt->execute([$id]);
        $money = $stmt->fetch(PDO::FETCH_COLUMN);
        $_SESSION['money'] = $money;
        return $money;
    }

    // 为用户添加头像
    public function setAvatar($path){
        $stmt = self::$pdo->prepare('UPDATE users set avatar=? where id=?');
        $stmt->execute([
            $path,
            $_SESSION['id']
        ]);
    }

    // ToolController 获取所有用户
    public function getAll(){
        $stmt = self::$pdo->query('SELECT * FROM users');
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 取出redis 排行榜
    public function getActiveUser(){
        $redis = \libs\Redis::getRedis();
        $data = $redis->Get('active_user');
        return json_decode($data,true);
    }

    // 把排行榜存入 redis中(计算活跃用户)
    public function setActiveUser(){
        // 计算用户分值
        $stmt = self::$pdo->query('SELECT user_id,count(*)*5 fen FROM blogs WHERE created_at <= DATE_SUB(CURDATE(), INTERVAL 1 WEEK) GROUP BY user_id');
        $data1 = $stmt->fetchAll(PDO::FETCH_ASSOC);
        // 评论分值
        $stmt = self::$pdo->Query('SELECT user_id,count(*)*5 fen FROM comments WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 1 WEEK) GROUP BY user_id');
        $data2 = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 点赞分值
        $stmt = self::$pdo->Query('SELECT user_id,count(*) fen FROM blog_zans WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 1 WEEK) GROUP BY user_id');
        $data3 =  $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 保存到数据
        $arr = [];

        foreach($data1 as $v){
            $arr[$v['user_id']] = $v['fen'];
        }
        foreach($data2 as $v){
            if(isset($arr[$v['user_id']])){
                $arr[$v['user_id']] += $v['fen'];
            }else {
                $arr[$v['user_id']] = $v['fen'];
            }
        }
        foreach($data3 as $v){
            if(isset($arr[$v['user_id']])){
                $arr[$v['user_id']] += $v['fen'];
            }else {
                $arr[$v['user_id']] = $v['fen'];
            }
        }

        // 倒序排序
        arsort($arr);

        // 取出前20 条保存键
        $data = array_slice($arr,0,20,true);
        // 从数组中取出前 20 键
        $user_id = array_keys($data);
        // 数组转为字符串
        $user_id = implode(',',$user_id);

        $stmt = self::$pdo->query("SELECT id,email,avatar FROM users WHERE id IN($user_id)");
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $redis = \libs\Redis::getRedis();
        $redis->set('active_user',json_encode($data));

    }


}