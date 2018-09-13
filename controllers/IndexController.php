<?php 
namespace controllers;
use models\Blog;

// 引入模型类
class IndexController {
    
    public function index(){
        $blog = new Blog;
        $blogs = $blog->getNew();
        view('index.index',['blogs'=>$blogs]);

    }

}