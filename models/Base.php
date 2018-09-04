<?php
// 所有模型的父模型
namespace models;
use PDO;

class Base {

    public static $pdo = null;

    // 保存pdo 对象
    public function __construct(){
        if(self::$pdo === null){
            // 读取配置文件
            $config = config('db');
            self::$pdo = new PDO('mysql:host='.$config['host'].';dbname='.$config['dbname'].'',$config['user'],$config['pass']);
            self::$pdo->exec('set names '.$config['charset']);
        }
    }

}