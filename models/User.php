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

            return TRUE;
        } else {
            return FALSE;
        }

    }

}