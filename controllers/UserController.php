<?php 
namespace controllers;
use models\User;

// 引入模型类
class UserController {

    public function hello(){

        $user = new User;
        $name = $user->getName();

        // 加载视图
        view('users.hello',[
            'name'=>$name
        ]);
    }

}