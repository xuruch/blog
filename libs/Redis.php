<?php

namespace libs;
// 单列redis类

class Redis {

    private static $redis = null;

    private function __clone(){}
    
    private function __construct(){}

    public static function getRedis(){

        // 连接 Redis
        if(self::$redis == null){
            // 读取配置文件
            $config = config('redis');

            self::$redis = new \Predis\Client($config);
        }
        return self::$redis;

    }

}