<?php 
namespace controllers;
class TestController {

    function test(){
        $redis = config('redis');
        echo "<pre>";
        var_dump($redis);
    }

}