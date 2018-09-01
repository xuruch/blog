<?php 
namespace controllers;
use models\Blog;
class BlogController {

    public function index(){

        $blogs = new Blog;

        $data = $blogs->search();

        view('blogs.index',$data);

    }

     // 为所有的日志生成详情页
     public function content_to_html(){
         $blog = new Blog;
         $blog->content2html();
    }

     // 生成index静态页
     public function index_to_html(){
        $blog = new Blog;
        $blog->index2html();
    }

    // 操作Redis
    public function update_display(){
        
        // 接收日志 id
        $id = (int)$_GET['id'];

        // 连接 Redis
        $redis = new \Predis\Client([
            'scheme' => 'tcp',
            'host'   => '127.0.0.1',
            'port'   => 6379,
        ]);
        // var_dump($redis);
        $key = "blog-{$id}";
        echo $key;
        $cc = $redis->hexists("blog_display",$key);
        // var_dump( $cc);
        // 判断Hash中是否有这个值
        if($cc){
            // 累加 并且返回累加后大值
            $newNum = $redis->hincrby('blog_display',$key,1);
            echo $newNum;
        }else {
            // 从数据库取出浏览量
            $blog = new Blog;
            $display = $blog->getDisplay($id);
            $display++;
            // 加到redis
            $redis->hsetnx('blog_display',$key,$display);
            echo $display;
        }

    }

}