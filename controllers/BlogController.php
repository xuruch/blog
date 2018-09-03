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
    public function display(){
        // 接收日志 id
        $id = (int)$_GET['id'];

        $blog = new Blog;
        echo $blog->getDisplay($id);
    }

    // 定期同步浏览量
    public function getDisplayDb(){

        $blog = new Blog;
        $blog->getDisplayDb();

    }

}