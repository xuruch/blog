<?php
namespace models;
use PDO;

class Comment extends Base {

    public function add($content,$blog_id){
        $stmt = self::$pdo->prepare('INSERT INTO comments(content,blog_id,user_id) values(?,?,?)');
        $stmt->execute([
            $content,
            $blog_id,
            $_SESSION['id']
        ]);
    }

    public function get($blog_id){
        $stmt = self::$pdo->prepare('SELECT c.*,u.email,u.avatar FROM comments c left join users u on c.user_id=u.id where c.blog_id=? order by c.id desc');
        $stmt->execute([$blog_id]);
        return $stmt->fetchAll( PDO::FETCH_ASSOC );
    }

}