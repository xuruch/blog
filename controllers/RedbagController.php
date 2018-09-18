<?php   
namespace controllers;
use models\Redbag;

class RedbagController {

    public function redbag(){
        view('redbags.redbag');
    }

    public function init(){

        $redis = \libs\Redis::getRedis();
        
        $redis->set('redbag_stock',20);
        $key = 'redbag_'.date('Ymd');
        $redis->sadd($key,'-1');
        $redis->expire($key,6000);

    }
    // 后台运行队列
    public function makeOrder(){
        $redis = \libs\Redis::getRedis();
        $rb = new Redbag;

        // 设置 socket 永不超时
        ini_set('default_socket_timeout', -1); 

        echo "开始监听红包队列... \r\n";

        while(true){

            $data = $redis->brpop('redbag_orders',0);
            $user_id = $data[1];
            $rb->create($user_id);
            echo "红包以抢到手、请注意查收";

        }
    }

    // 抢红包
    public function qRed(){
        // 判断有咩有登录
        if(!isset($_SESSION['id'])){
            echo json_encode([
                'status_code' => '401',
                'message' => '登录后才能抢红包哦！！'
            ]);
            exit;
        }
        // 判断是否为9~10点之间
        // if(date('H') < 8 || date('H') > 23){
        //     echo json_encode([
        //         'status_code' => '403',
        //         'message' => '只有上午9~10点段之间才能抢红包哦！！'
        //     ]);
        //     exit;
        // }
        
        // 判断今天是否抢过
        $key = 'redbag_'.date('Ymd');
        $redis = \libs\Redis::getRedis();
        $exists = $redis->sismember($key,$_SESSION['id']);
        if($exists){
            echo json_encode([
                'status_code' => '403',
                'message' => '今天已经抢过了哦！！'
            ]);
            exit;
        }
        // echo '111';die;  

        // 减少本次抢过红包的库存、并返回库存被减完的 值
        $stock = $redis->decr('redbag_stock');
        // var_dump($stock);
        if($stock < 0) {
            echo json_encode([
                'status_code' => '403',
                'message' => '今天的红包已经抢完了！！'
            ]);
            exit;
        }

        // 下单（放到队列）
        $redis->lpush('redbag_orders',$_SESSION['id']);
        // 吧抢红包账户的 ID 放到集合（表示抢过了）
        $redis->sadd($key,$_SESSION['id']);
        echo json_encode([
            'status_code' => '200',
            'message' => '恭喜你、抢到本站红包'
        ]);
    }


}