<?php
namespace controllers;
use models\Comment;
class CommentController {

    // 发评论
    public function comments(){

        // 接收原始数据
        $data = file_get_contents('php://input');
        // 转成数组
        $_POST = json_decode($data, TRUE);

        // 判断用户是否登录
        if(!isset($_SESSION['id'])) {
            echo josn_encode([
                'status_code' => 401,
                'message' => '必须先登录'
            ]);
            exit;
        }
        // 接受表单中的数据
        $content = e($_POST['content']);
        $blog_id = $_POST['blog_id'];

        // 插入到评论表中
        $comment = new Comment;
        $comment->add($content,$blog_id);
        
        // 返回新发表的评论 （数据过滤）
        echo json_encode([
            'status_code' => 200,
            'message' => '发表成功',
            'data' => [
                'content' => $content,
                'avatar' => $_SESSION['avatar'],
                'email' => $_SESSION['email'],
                'cerated_at' => date('Y-m-d H:i:s')
            ]
        ]);
        exit;
    }

    // 获取评论
    public function get(){
        $blog_id = $_GET['id'];
        $comment = new Comment;
        $data = $comment->get($blog_id);
        // echo "<pre>";
        echo json_encode([
            'status_code' => 200,
            'data' => $data
        ]);
        die;
    }

}