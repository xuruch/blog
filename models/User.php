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


}