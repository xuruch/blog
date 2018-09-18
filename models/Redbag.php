<?php
namespace models;
use PDO;

class Redbag extends Base {

    public function create($user_id){
        $stmt = self::$pdo->prepare('INSERT INTO redbags(user_id) values(?)');
        $stmt->execute([$user_id]);
    }

}