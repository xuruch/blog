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
        $display = $blog->getDisplay($id);
        echo json_encode([
            'display'=>$display,
            'email'=>isset($_SESSION['email']) ? $_SESSION['email'] : ''
        ]);
    }

    // 定期同步浏览量
    public function getDisplayDb(){

        $blog = new Blog;
        $blog->getDisplayDb();

    }

    // 发表日志
    public function create(){
        if($_SESSION['emain']){
            echo "<script>alert('你还未登录')</script>";
            redirect('/user/login');
        }
        view('blogs.create');
    }

    public function add_blog(){
        $title = $_POST['title'];
        $content = $_POST['content'];
        $is_show = $_POST['is_show'];

        $blog = new Blog;
        $id = $blog->addBlog($title,$content,$is_show);

        if($id){
            if($is_show==1){
                $blog->addHtml($id);
            }
            message('发表成功',2,'/blog/index');

        }else {
            die('发表失败');
        }

    }
    
    // 删除日志
    public function delete(){
        $id = $_POST['id'];
        $blog = new Blog;
        $blog->delete($id);
        $blog->deleteHtml($id);
        message('删除成功',2,'/blog/index');
    }

    // 修改日志 显示页面
    public function change(){
        $id = $_GET['id'];
        $blog = new Blog;
        $change_g = $blog->change($id);
        view('blogs.change',$change_g);
    }
    // 修改日志
    public function update(){
        $id = $_POST['id'];
        $title = $_POST['title'];
        $content = $_POST['content'];
        $is_show = $_POST['is_show'];
        $blog = new Blog;
        $blog->update($title,$content,$is_show,$id);
        if($is_show==1){
            $blog->addHtml($id);
        }
        message('修改成功！', 0, '/blog/index');
    }

    // 显示私有页面
    public function content(){
        $id = $_GET['id'];
        $blogs = new Blog;
        $blog = $blogs->find($id);
        // var_dump($blog,$blogs);die;

        if($_SESSION['id'] != $blog['user_id']){
            die('你无权访问、这是私人日志');
        }
        view('blogs.content',[
            'blog'=>$blog
        ]);
    }



}