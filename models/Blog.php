<?php
namespace models;
use PDO;

class Blog {

    protected $pdo;

    public function __construct(){
        // 连接数据库
        $this->pdo = new PDO('mysql:host=127.0.0.1;dbname=blog','root','198211');
        $this->pdo->exec('set names utf8');
    } 

    public function search(){
        // 设置where 值
        $where = 1;
        // 初始化预处理
        $value = [];

        // 关键词搜索
        if(isset($_GET['keyword']) && $_GET['keyword']){
            $where .= " AND (title like ? or content like ?)";
            $value[] = '%'.$_GET['keyword'].'%';
            $value[] = '%'.$_GET['keyword'].'%';
        }
        // 日期搜索
        if(isset($_GET['statr_date']) && $_GET['statr_date']){
            $where .= " AND created_at >= ?";
            $value[] = $_GET['statr_date'];
        }
        if(isset($_GET['end_date']) && $_GET['end_date']){
            $where .= " AND updated_at <= ?";
            $value[] = $_GET['end_date'];
        }
        // 是否显示
        if(isset($_GET['is_show']) && ($_GET['is_show']==='1' || $_GET['is_show']==='0')){
            $where .= " AND is_show = ?";
            $value[] = $_GET['is_show'];
        }

        // 排序方式
        $odby = 'created_at';
        $odway  = 'desc';
        if(isset($_GET['odby']) && $_GET['odby']=='display'){
            $zx = 'display';
        }
        if(isset($_GET['odway ']) && $_GET['odway ']=='asc'){
            $odway  = 'asc';
        }

        $perpage = 15;
        // 接收当前页码（大于等于1的整数）， max：最参数中大的值
        $page = isset($_GET['page']) ? max(1,(int)$_GET['page']) : 1;
        // 计算开始的下标
        $offset = ($page-1)*$perpage;
        // 取出总的记录数
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM blogs WHERE $where");
        $stmt->execute($value);
        $count = $stmt->fetch( PDO::FETCH_COLUMN );
        // 计算总的页数（ceil：向上取整（天花板）， floor：向下取整（地板））
        $pageCount = ceil( $count / $perpage );
        $btns = '';
        for($i=1; $i<=$pageCount; $i++)
        {
            // 先获取之前的参数
            $params = getUrlParams(['page']);
            $class = $page==$i ? 'active' : '';
            $btns .= "<a class='$class' href='?{$params}page=$i'> $i </a>";
            
        }

        $stmt = $this->pdo->prepare("select * from blogs where $where order by $odby $odway limit $offset,$perpage");
        $stmt->execute($value);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            "data"=>$data,
            "btns"=>$btns
        ];
    }

    // 生成静态内容页
    public function content2html(){
        $stmt = $this->pdo->query('select * from blogs');
        $blogs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        ob_start();
        foreach($blogs as $v){
            view('blogs.content',[
                'blog'=>$v
            ]);
            // exit;
            // 获取页面
            $str = ob_get_contents();
            // 保存
            file_put_contents(ROOT.'/public/contents/'.$v['id'].'.html',$str);
            // 清空缓冲区
            ob_clean();
        }
    }

    // 生成index静态页
    public function index2html(){
        // 取前20条数据
        $stmt = $this->pdo->query("SELECT * FROM blogs WHERE is_show=1 ORDER BY id DESC LIMIT 20");
        $blogs = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 开启ob缓冲区
        ob_start();

        // 加载视图文件到缓冲区
        view('index.index', [
            'blogs' => $blogs,
        ]);

        // 从缓冲区取出页面
        $str = ob_get_contents();

        file_put_contents(ROOT.'/public/index.html',$str);

        ob_clean();
    }


    // 从数据库中取出日志的浏览量
    public function getDisplay($id){

        // 取出日志id并评出键名
        $key = "blog-{$id}";
        
        // 连接 Redis
        $redis = new \Predis\Client([
            'scheme' => 'tcp',
            'host'   => '127.0.0.1',
            'port'   => 6379,
        ]);

        // 判断Hash中是否有这个值
        if($redis->hexists("blog_display",$key)){
            // 累加 并且返回累加后大值
            $newNum = $redis->hincrby('blog_display',$key,1);
            return $newNum;
        }else {
            // 从数据库取出浏览量
            $stmt = $this->pdo->query("SELECT display FROM blogs WHERE id = $id");
            $display =  $stmt->fetch(PDO::FETCH_COLUMN);
            $display++;
            // 加到redis
            $redis->hsetnx('blog_display',$key,$display);
            return $display;
        }
    }

    // 获取日志的浏览量
    public function getDisplayDb(){

        // 连接 Redis
        $redis = new \Predis\Client([
            'scheme' => 'tcp',
            'host'   => '127.0.0.1',
            'port'   => 6379,
        ]);

        $data = $redis->hgetall('blog_displays');

        foreach($data as $k => $v){
            $id = str_replace('blog-','',$k);
            $sql = "UPDATE blogs set display={$v} where id = {$id}";
            $this->pdo->exec($sql);
        }
    }

}