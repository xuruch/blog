<?php 
namespace models;
use PDO;
class User extends Base {
        
    // 添加用户名
    public function add($email,$pwd){
        $stmt = self::$pdo->prepare("INSERT INTO users(email,password) VALUES(?,?)");
        return  $stmt->execute([
                    $email,
                    $pwd
                ]);
    }

}