<?php
namespace models;
use PDO;

class Blog extends Base {

    public function search(){
        // 设置where 值
        $where = 1;
        $where = 'user_id='.$_SESSION['id'];
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
        // 接收当前页码
        $page = isset($_GET['page']) ? max(1,(int)$_GET['page']) : 1;
        // 计算开始的下标
        $offset = ($page-1)*$perpage;
        // 取出总的记录数
        $stmt = self::$pdo->prepare("SELECT COUNT(*) FROM blogs WHERE $where");
        $stmt->execute($value);
        $count = $stmt->fetch( PDO::FETCH_COLUMN );
        // 计算总的页数
        $pageCount = ceil( $count / $perpage );
        $btns = '';
        for($i=1; $i<=$pageCount; $i++)
        {
            // 先获取之前的参数
            $params = getUrlParams(['page']);
            $class = $page==$i ? 'active' : '';
            $btns .= "<a class='$class' href='?{$params}page=$i'> $i </a>";
            
        }

        $stmt = self::$pdo->prepare("select * from blogs where $where order by $odby $odway limit $offset,$perpage");
        $stmt->execute($value);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            "data"=>$data,
            "btns"=>$btns
        ];
    }

    // 生成静态内容页
    public function content2html(){
        $stmt = self::$pdo->query('select * from blogs');
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
        $stmt = self::$pdo->query("SELECT * FROM blogs WHERE is_show=1 ORDER BY id DESC LIMIT 20");
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
        $redis = \libs\Redis::getRedis();

        // 判断Hash中是否有这个值
        if($redis->hexists("blog_display",$key)){
            // 累加 并且返回累加后大值
            $newNum = $redis->hincrby('blog_display',$key,1);
            return $newNum;
        }else {
            // 从数据库取出浏览量
            $stmt = self::$pdo->query("SELECT display FROM blogs WHERE id = $id");
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
        $redis = \libs\Redis::getRedis();

        $data = $redis->hgetall('blog_displays');

        foreach($data as $k => $v){
            $id = str_replace('blog-','',$k);
            $sql = "UPDATE blogs set display={$v} where id = {$id}";
            self::$pdo->exec($sql);
        }
    }


    // 发表日志
    public function addBlog($title,$content,$is_show){

        $stmt = self::$pdo->prepare("INSERT INTO blogs(title,content,is_show,user_id) VALUES(?,?,?,?)");
        $res = $stmt->execute([
                $title,
                $content,
                $is_show,
                $_SESSION['id']
        ]);
        if(!$res){
            echo "发表失败";
            $error = $stmt->errorInfo();
            echo "<pre>";
            var_dump($error);
            die;
        }
        return self::$pdo->lastInsertId();
    }

    // 删除日志
    public function delete($id){
        // 只能删除自己的日志
        $stmt = self::$pdo->prepare('DELETE FROM blogs WHERE id = ? AND user_id=?');
        $stmt->execute([
            $id,
            $_SESSION['id'],
        ]);
    }

    // 修改日志
    public function change($id){
        $stmt = self::$pdo->prepare("SELECT * from blogs where id = ?");
        $stmt->execute(array($id));
        $change_g = $stmt->fetch(PDO::FETCH_ASSOC);
        return [
            'change_g'=>$change_g
        ];
    }

    // 修改日志
    public function update($title,$content,$is_show,$id){
        $stmt = self::$pdo->prepare('UPDATE blogs set title=?,content=?,is_show=? where id=?');
        $stmt->execute([
            $title,
            $content,
            $is_show,
            $id
        ]);
    }

    public function find($id)
    {
        $stmt = self::$pdo->prepare('SELECT * FROM blogs where id = ?');
        $stmt->execute([
            $id
        ]);
        return $stmt->fetch();
    }
    // 修改或添加日志的时候生成静态页
    public function addHtml($id){
        // 取出这次插入日志的ID信息
        $blog = $this->find($id);
        ob_start();
        view('blogs.content',[
            'blog'=>$blog
        ]);
        $str = ob_get_clean();
        file_put_contents(ROOT.'/public/contents/'.$id.'.html',$str);

    }
    // 删除静态页
    public function deleteHtml($id)
    {
        // @ 防止 报错：有这个文件就删除，没有就不删除，不用报错
        @unlink(ROOT.'public/contents/'.$id.'.html');
    }

    // 取出20条日志放在首页
    public function getNew(){
        $stmt = self::$pdo->query('SELECT * FROM blogs WHERE is_show=1 ORDER BY id DESC LIMIT 20');
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 点赞 
    public function zan($blog_id){
        // 判断是否点过赞
        $stmt = self::$pdo->prepare("SELECT count(*) from blog_zans where user_id=? and blog_id=? ");
        $stmt->execute([
                $_SESSION['id'],
                $blog_id
        ]);
        $count = $stmt->fetch(PDO::FETCH_COLUMN);
        if($count == 1){
            return false;
        }
        // 点赞
        $stmt = self::$pdo->prepare('INSERT INTO blog_zans(user_id,blog_id) VALUES(?,?)');
        $zan =  $stmt->execute([
                    $_SESSION['id'],
                    $blog_id
                ]);
           
        // 更行点赞数
        if($zan){
            $stmt = self::$pdo->prepare('UPDATE blogs set zan=zan+1 where id=?');
            $a =$stmt->execute([$blog_id]);

        }
        return $zan;
    }
    // 点赞列表显示
    public function zan_list($id){
        $stmt = self::$pdo->prepare('SELECT u.id,u.email,u.avatar from blog_zans bz left join users u on bz.user_id=u.id where bz.blog_id=?');
        $stmt->execute([$id]);
        return $stmt->fetchAll( PDO::FETCH_ASSOC );
    }

}