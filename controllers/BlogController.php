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

    // 发表日志
    public function create(){
        view('blogs.create');
    }

    public function add_blog(){
        $title = $_POST['title'];
        $content = $_POST['content'];
        $is_show = $_POST['is_show'];

        $blog = new Blog;
        $b = $blog->addBlog($title,$content,$is_show);

        if($b){
            message('发表成功',2,'/blog/index');
        }else {
            die('发表失败');
        }

    }
    
    // 删除日志
    public function delete(){
        $id = $_GET['id'];
        $blog = new Blog;
        $blog->delete($id);
        message('删除成功',2,'/blog/index');
    }

    // 修改日志
    public function change(){
        $id = $_GET['id'];
        $blog = new Blog;
        $change_g = $blog->change($id);
        view('blogs.change',$change_g);
    }

}