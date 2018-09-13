<?php 
namespace controllers;
use Yansongda\Pay\Pay;
use models\Order;

class AlipayController {

    public $config = [
        'app_id' => '2016091600527019',
        // 通知地址
        'notify_url' => 'http://xrc.tunnel.echomod.cn/alipay/notify',
        // 跳回地址
        'return_url' => 'http://localhost:9999/alipay/return',
        // 支付宝公钥
        'ali_public_key' => 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAr/jyxxdRLaFfBt8s+Ndm9/IrmYB1NAcZp7gB37s6s04UXxoRzdPQvpLRAFozZUuEJmxSV8GEFb3AstAJHUEW//c7N6oWnbxt2nwVcY/7rwSa5gaH/FK/HnBY0cVk55x2cGAOE0IHQ0SL+kyjKhcLjo9VCJrm2T9s9gd5qHcg0GFJ1a9r7kx7Uk8BgvFfU+9Lz039EWHxzLQsZmQ+L9f/cUN2oI8z06c9FaiiUJGbQ9uUDLi5sZ+e9/iyhstCetI6+TzCHFpPvsUFxvunIMonITTZaanBMSSn7kU7Xl5EzTPMcRM5CKluzK9rXX4H1iJWMTUDH6yWS1s8zk+BvPfj6wIDAQAB',
        // 商户应用密钥
        'private_key' => 'MIIEowIBAAKCAQEAo5kg3KJ4WFoaOBKTs04JxHQ4wcSgfl7Q7E8f8uNTmFZLkRqHnrXFCblkgrc6IzokO8lxs+G2/gnkosE8jI8oRm32SpSbND8NMcGvQ9TNXdQah6BOJjTAhWd559xRJBcyt/axPQ5FD9z6hhAyh8uUHRn9WkR6qiw2z/DUTfMeGTAGNnemd7L0EkE3nsuUyWRh/vzkOKG/WiWfxfoUJu64im9+PWJlqiFD9RwyOMitYvWAVPMp/E6hiLb4TLtW3QcEi4B9CFrZgHyZYevtoTP+vkmvYDBFnVNQyEBofYzOs0e3o9QDvYRDN4sUAnp/8YEkt+z5m+qCDcb5JNyEFk3hHwIDAQABAoIBAQCf1hYzOlFWEQIY0p2Xy6DjXaGuPwuHK9/3YP8X8V5VMcq2xDLIr1MJQyaeR6H3lPCdsRzz4I1G+id1lFIyChQRa5H8H+DbVNbxTAiOvzS+fWJGLCruTQ/jxstl6u0j075r2gwkWPLEouPpY53a6t2U6TWMoecup/ANjX//gI5hj+gKohe9N1HDAXcf22P70A84bywHSrEm32Bv36HrWL6kbxnHhpM5wmsD0f4l8CuWVHKDDc0uVpenTXIkk2X3Qjx+y9in4RVRS160iLc4fUuzwtG0UVanwT4XTb2EMV1IqdRK6MYP61UOtNU/GwPsimbl9I+DD4Z5qsNsNuhYI8iRAoGBANM2aHLza+26LlwJNRXjSIMmTE+TxcId2vIcLVu/79GlGPXuKPfECbJ/hnun6T3qZLYzvB0K7z5xAX0YvXLm/pkR/PxO6DI0goP2nITVrlfLLSgaRewaLoZN5ihrx5ox9JiBuuHgh62l+MqjjJQLgVljY5n/uBPU6taDAZPCDUZbAoGBAMZJ/f/znvrJxHhQRjjSqWyLycp9J7IpExlkS8YKPpmhoHT64u5LFAG71fqpijqxljMdUdIxn5bJ7tcxH3j6AZeNynKgy+UXHnFP/0/Xhbs5Uw6W0TzPMkwWRaol4Jw/mVtbpqNYAN/UZYQKIModtTEEYmTVTVTw8dUvRILVDjONAoGAdJ0dnhn4BRE+d2I+BQ+wAXkruOQF099X+0TDZlzatA1Lcr1DsUhzcjImti9HAABiCpcVzZMz9G3APKlkMASJnUnpPAT0/oMvbFzEnjK8R4zOKc4XvPvXpB9ua/LWCbR7L5iw7fVh8+YnLvqNq0+O4yOU4nnl0E1GO5mpTUNewPECgYA34UBAckJ6QIQqt8yiVNPEMDzE4LJgJe0o+bfU7qwnlYHnlZCAQQ45UtsBkefPlJ5Ud03Io41q7Ctv33sUF7h9IktMNH5haDt/CWFU+dLJKWV7tgrvTSDKinhFc/kxb0bjlReH7tQIQA3/wO7upqKaVrb33Zi2eFRelLyE9k/TPQKBgGzs+sZ9NCDx+BZInbCjf9C9R81SNLWkZSbj56ASSSbOgCzyGxgyxwLqkZaSnM7O//Jz3uUmWAaOpfUp0YdO5sukZLza097KdbRJnB5ZPQLOu4hSDIa66useFr5ALdMbsLZcEoj+8mlTHOSKUPnQjCg3Y3fvwGwbv3GNvdIhj+VU',
        // 沙箱模式（可选）
        'mode' => 'dev',
    ];

    public function pay(){
        // 接收订单编号
        $sn = $_POST['sn'];
        // 取出订单信息
        $order = new \models\Order;
        // 根据订单编号取出订单信息
        $data = $order->findBySn($sn);

        // 如果订单还未支付就跳到支付宝
        if( $data['status'] == 0 ){
            // 跳转到支付宝
            $alipay = Pay::alipay($this->config)->web([
                'out_trade_no' => $sn,
                'total_amount' => $data['money'],
                'subject' => '智聊系统用户充值-'.$data['money'].'元',
            ]);
            $alipay->send();
        }else{
            die('订单状态不允许支付~');
        }

    }

    // 支付完成跳回
    public function return(){
        $data = Pay::alipay($this->config)->verify();
        // var_dump($dalta);die;
        echo '<h1>支付成功了</h1> <hr>';
        var_dump( $data->all() );
    }

    // 接收支付完成的通知
    public function notify() {

        // 生成支付类的对象
        $alipay = Pay::alipay($this->config);
        // var_dump($alipay);die;
        try{
            $data = $alipay->verify();

            // 判断支付状态
            if($data->trade_status == 'TRADE_SUCCESS' || $data->trade_status == 'TRADE_FINISHED'){
                // 更新订单状态
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
                    // $ret2 = $user->addMoney($orderInfo['money'], $orderInfo['user_id']);

                    if($ret1 && $ret2){
                       $order->commit(); 
                    }else {

                        $order->rollback();
                        // $this->refund($data->out_trade_no);

                    }
                }
            }
        } catch (\Exception $e) {
            die('shibai');
        }
        // 回应支付宝服务器
        $alipay->success()->send();
        
    }


    // 退款
    public function refund($sn) {
        // 生成退款订单号
        $refundNo = md5( rand(1,99999) . microtime() );

        // $sn = $_POST['sn'];
        $order = new \models\Order;
        $data = $order->findBySn($sn);
        // echo "dd";
        // var_dump($data);die;
        try{
            // 退款
            $ret = Pay::alipay($this->config)->refund([
                'out_trade_no' => $data['sn'],  
                'refund_amount' => $data['money'],         
                'out_request_no' => $refundNo,     
            ]);
            if($ret->code == 10000){
   
                $order->fail($data['user_id'],$data['money'],$data['status'],$data['sn']);
                $order->delete_refund($data['sn']);
                message("退款成功",2,'/user/orders');
            }
        }
        catch(\Exception $e){
            var_dump( $e->getMessage() );
        }
    }

    public function order_fail(){
        // $order = new Order;
        // $this->refund();

        // $data = $order->findBySn('258692310038999040');
                        
    }

}