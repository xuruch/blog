<?php
namespace models;
use PDO;
class Order  extends Base {

    // 下订单
    public function create($money){

        $flake = new \libs\Snowflake(1023);

        $stmt = self::$pdo->prepare('INSERT INTO orders(user_id,money,sn) values(?,?,?) ');
        $n = $stmt->execute([
            $_SESSION['id'],
            $money,
            $flake->nextId()
        ]);
        // var_dump($n);die;
    }

    // 搜索订单
    public function search(){
        // 取出当前用户的订单
        $where = 'user_id='.$_SESSION['id'];
        // 排序
        $odby = 'created_at';
        $odway = 'desc';
        // 分页
        $perpage = 15;
        $page = isset($_GET['page']) ? max(1,(int)$_GET['page']) : 1;
        $offset = ($page-1)*$perpage;
        $stmt = self::$pdo->prepare("SELECT COUNT(*) FROM orders WHERE $where");
        $stmt->execute();
        $count = $stmt->fetch( PDO::FETCH_COLUMN );
        $pageCount = ceil( $count / $perpage );

        $btns = '';
        for($i=1; $i<=$pageCount; $i++)
        {
            // 先获取参数
            $params = getUrlParams(['page']);
            $class = $page==$i ? 'active' : '';
            $btns .= "<a class='$class' href='?{$params}page=$i'> $i </a>";
            
        }
        $stmt = self::$pdo->prepare("SELECT * FROM orders WHERE $where ORDER BY $odby $odway LIMIT $offset,$perpage");
        $stmt->execute();

        // 取数据
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return [
            'btns' => $btns,
            'data' => $data,
        ];
    }

    // 模型中添加方法取数据
    // 根据编号从数据库中取出订单信息
    public function findBySn($sn){
        $stmt = self::$pdo->prepare('SELECT * FROM orders WHERE sn=?');
        $stmt->execute([
            $sn
        ]);
        return $stmt->fetch( PDO::FETCH_ASSOC );
    }

    // 设置订单为已支付的状态
    public function setPaid($sn){
        $stmt = self::$pdo->prepare("UPDATE orders SET status=1,pay_time=now() WHERE sn=?");
        return  $stmt->execute([
                    $sn
                ]);
    }

    // 退款 如果退款成功就删除单号
    public function delete_refund($sn){
        $stmt = self::$pdo->prepare("delete from orders where sn = ?");
        return  $stmt->execute([
                    $sn
                ]);
        
    }
    // 把失败的订单放入订单失败表 orders_fail
    public function fail($user_id,$money,$status,$sn){  
        $stmt = self::$pdo->prepare('INSERT INTO orders_fail(user_id,money,status,sn) values(?,?,?,?)');
        $stmt->execute([
            $user_id,
            $money,
            $status,
            $sn
        ]);
    }
}