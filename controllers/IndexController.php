<?php 
namespace controllers;
use models\Blog;
use models\User;

// 引入模型类
class IndexController {
    
    public function index(){
        $blog = new Blog;
        $blogs = $blog->getNew();

        $user = new User;
        $users = $user->getActiveUser();

        view('index.index',[
                'blogs'=> $blogs,
                'users' => $users
            
            ]);


    }



}