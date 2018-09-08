<?php 
namespace controllers;
use Yansongda\Pay\Pay;

class AlipayController {

    public $config = [
        'app_id' => '2016091600527019',
        // 通知地址
        'notify_url' => 'http://requestbin.fullcontact.com/r6s2a1r6',
        // 跳回地址
        'return_url' => 'http://localhost:9999/alipay/return',
        // 支付宝公钥
        'ali_public_key' => 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAr/jyxxdRLaFfBt8s+Ndm9/IrmYB1NAcZp7gB37s6s04UXxoRzdPQvpLRAFozZUuEJmxSV8GEFb3AstAJHUEW//c7N6oWnbxt2nwVcY/7rwSa5gaH/FK/HnBY0cVk55x2cGAOE0IHQ0SL+kyjKhcLjo9VCJrm2T9s9gd5qHcg0GFJ1a9r7kx7Uk8BgvFfU+9Lz039EWHxzLQsZmQ+L9f/cUN2oI8z06c9FaiiUJGbQ9uUDLi5sZ+e9/iyhstCetI6+TzCHFpPvsUFxvunIMonITTZaanBMSSn7kU7Xl5EzTPMcRM5CKluzK9rXX4H1iJWMTUDH6yWS1s8zk+BvPfj6wIDAQAB',
        // 商户应用密钥
        'private_key' => 'MIIEpQIBAAKCAQEAxOYZ+1ssd5/+BPY1//52KzdT5dwjm64OBJc/pjG7Zav6hBURiAxKsEY2jUvkrIe4gUg8/hQOOZCCccdIs8kIjfToDe/+3NQs3pbQggVsEdGyzeDt4kKXL91zq5tX+yHybZeLQd5U3Ka03u1ChzsszohWzu2EYxbG5bRIuhSgxDvR1gSzDnV5cu+ACqtj/6H3ZLG4GaBmJhqj6Pqi8cknsSxyYzJnpyNZ8swW+noDmaJz3cVSCl63m4/HSM+OGUplhQ4VXSyBBMQVu4p+6fTGr0MNVQ30EIIib2mXFn7nv7J1blN62UaPHDSk4IcjR5XqzaPdp6nZhve0T9U9QqEyhQIDAQABAoIBAQCK6gB4qsmDmFgh7gcJFeEO+ljxuc9RfYocSQ051qpsv9ndp/OXdXyYQuEn7NxnsNVGOM33P/UNPdwaI8wyM2oapSe29ZRs/jCt1d9DbEnjhHvO7ptGX07FEUsTTmTTJA8irKEuForupZrEEMY4HFKvX9dG5KHbOu2WkAwjZHxMFN9DPW9TZR/EGtefNajoLxHKKRWR+AGmnfYQU3VmlewkkeFBQn4fykHso3kXx4shbufiwJ6SekJBHC2M6Tfk+X27tq33PeBnh6Z7wl61oPxP4LKPgTdkmXuzwtCuVL3AJj8fH0rA1p9aFZudm94nvrMHUfBFk9fL5/kKLdWA3JWBAoGBAOXNXaR0IClHtwCjEnjyhHySw1AbIBKnvq/6l25sHdXrKkTeisRCRAy0H2UtSu4MqwrJnYd6vr8qw2yi1KYHFdrp4d1JhXjJNhbS6qa/9yFLNpF5qVXX9mSjxXbwT4Z3s/VLVw5le6v13j0m4Q7k/FgJgs+7WyDU8QySo8DsAiXhAoGBANtYeJ+LFpe2ctExQM5+bHX3+BMk4OCIJQlDMhc3WGuIcLxFGb3m8v3mKa/UO/DdWnzf0eBp8AQSN9WP+oO2RC8nzWqCzACq0dkKfj7CSOPYb30Orj7boTpFH9RVnZZfh9eacBdiyB+Z2YhExxAhLHJFbA/srBtVPOSY9j6u9tklAoGBANp1dtYFxyU3FdO3MrJj6mr6ok4e20igyvhEg3znrx1TqCU8YjLcxBBHk6j9e8P9qwRgUi8fEwJsxscZrIoBJlesC5nwcMA4mADulT/cMjUmaUO2orIG0icuQWQmY5NzbFJ+Qs5ez0jUPWdo9H7SVMnkkMSmWaVGibjoFmAqfIhBAoGBAJzyL9UQo4jIjl0qndFi2l6KoGGqWJGwOLfo8bV073p59RhxZRrhXmrXvmpGNOGCsRV63ORzSL7Q/kx8KecGisEm/K9CiY/MqjxfarejMp59JkDYOUtWwYU9Iqo+3HRVRJC7u4qwLruVzPXZKIpdAj19ePXoBbnFAr4ayM9PR+dVAoGAT0Ua/7s2N1db7KoDV33nO2Tc6g5ijEWKputCO/iQK3P6TNX7Bcg7wnPGmHyR3MazSVXpXtZrFy9OFV4ulEugO/6cByiv5bxkO1qnRBXgX8fYI3oIpeupRvFzvpBfju+RP+ILIV8GRvJD19Iu5ppWNAI46OmgLUj98JR2lp2Gg6w=',
        // 沙箱模式（可选）
        'mode' => 'dev',
    ];

    public function pay(){
        $order = [
            'out_trade_no' => time(),    // 本地订单ID
            'total_amount' => '1',    // 支付金额
            'subject' => 'test subject', // 支付标题
        ];

        $alipay = Pay::alipay($this->config)->web($order);
        $alipay->send();
    }

    // 支付完成跳回
    public function return(){
        $data = Pay::alipay($this->config)->verify();
        echo '<h1>支付成功！</h1> <hr>';
        var_dump( $data->all() );
    }

    // 接收支付完成的通知
    public function notify() {
        $alipay = Pay::alipay($this->config);
        try{
            $data = $alipay->verify();
            echo '订单ID：'.$data->out_trade_no ."\r\n";
            echo '支付总金额：'.$data->total_amount ."\r\n";
            echo '支付状态：'.$data->trade_status ."\r\n";
            echo '商户ID：'.$data->seller_id ."\r\n";
            echo 'app_id：'.$data->app_id ."\r\n";
        } catch (\Exception $e) {
            echo '失败：';
            var_dump($e->getMessage()) ;
        }
        // 返回响应
        $alipay->success()->send();
    }


    // 退款
    public function refund() {
        // 生成退款订单号
        $refundNo = md5( rand(1,99999) . microtime() );
        var_dump($refundNo);
        die;
        try{
            // 退款
            $ret = Pay::alipay($this->config)->refund([
                'out_trade_no' => '1536227456',  
                'refund_amount' => 1,         
                'out_request_no' => $refundNo,     
            ]);
 
            if($ret->code == 10000)
            {
                echo '退款成功！';
            }
        }
        catch(\Exception $e)
        {
            var_dump( $e->getMessage() );
        }
    }

}