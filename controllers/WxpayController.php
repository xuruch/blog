<?php
namespace controllers;
use Yansongda\Pay\Pay;
use Endroid\QrCode\QrCode;

class WxpayController {

    protected $config = [
        'app_id' => 'wx426b3015555a46be',
        'mch_id' => '1900009851',
        'key' => '8934e7d15453e97507ef794cf7b0519d',
        'notify_url' => 'http://xrc.tunnel.echomod.cn/wxpay/notify'
    ];

    public function pay(){

        $sn = $_POST['sn'];
        $order = new \models\Order;
        $data = $order->findBySn($sn);
        if($data['status'] == 0){
            $ret = Pay::wechat($this->config)->scan([
                'out_trade_no' => $data['sn'],
                'total_fee' => $data['money'] * 100,
                'body' => '智聊系统用户充值 ：'.$data['money'].'元',
            ]);

            if($ret->result_code == 'SUCCESS' && $ret->return_code == 'SUCCESS'){
        var_dump($ret);die;

                view('users.wxpay', [
                    'code' => $ret->code_url,
                    'sn' => $sn,
                ]);
            }
        }else{
            die('订单状态不允许支付~');
        }
    }

    public function notify(){
        $pay = Pay::wechat($this->config);
        
        try{
            $data = $pay->verify();
            if($data->result_code == 'SUCCESS' && $data->return_code == 'SUCCESS'){
                $order = new \models\Order;
                // 获取订单信息
                $orderInfo = $order->findBySn($data->out_trade_no);
                if($orderInfo['status'] == 0){

                    // 开启事物
                    $order->startTrans();
                    // 设置订单为已支付状态
                    $ret1 = $order->setPaid($data->out_trade_no);
                    // 为用户增加钱数
                    $user = new \models\User;
                    $ret2 = $user->addMoney($orderInfo['money'], $orderInfo['user_id']);

                    if($ret1 && $ret2){
                       $order->commit(); 
                    }else {
                        $order->rollback();
                        $this->refund($data->out_trade_no);
                    }
                }
            }
        }catch(\Exception $e){
            $log->log('验证失败！' . $e->getMessage());
            var_dump( $e->getMessage() );
            die('shibai');
        }
        $pay->success()->send();
    }

    // 把字符窜生产为二维码
    public function qrcode(){
        $str = $_GET['code'];
        $qrCode = new QrCode($str);
        header('Content-Type: '.$qrCode->getContentType());
        echo $qrCode->writeString();
    }

}