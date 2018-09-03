<?php 
namespace models;
use PDO;

class User {

    public $pdo;

    public function __construct(){
        $this->pdo = new PDO('mysql:host=localhost;dbname=blog','root','198211');
        $this->pdo->exec('set names utf-8');
    }

    // 添加用户名
    public function add($email,$pwd){
        $stmt = $this->pdo->prepare("INSERT INTO users(email,password) VALUES(?,?)");
        return  $stmt->execute([
                    $email,
                    $pwd
                ]);
    }

}