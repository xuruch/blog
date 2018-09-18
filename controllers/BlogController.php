<?php 
namespace controllers;
use models\Blog;
use PhpOffice\PhpSpreadsheet\Spreadsheet; //Excel
use PhpOffice\PhpSpreadsheet\Writer\Xlsx; //Excel

class BlogController {

    public function index(){
        $blogs = new Blog;
        $data = $blogs->search();
        view('blogs.index',$data);

    }

    // 导出Excel
    public function makeExcel(){
        // 获取当前标签页
        $spreadsheet = new Spreadsheet();
        // 获取当前工作
        $sheet = $spreadsheet->getActiveSheet();

        // 设置第1行内容
        $sheet->setCellValue('A1', '标题');
        $sheet->setCellValue('B1', '内容');
        $sheet->setCellValue('C1', '发表时间');
        $sheet->setCellValue('D1', '是发公开');

        $model = new \models\Blog;
        $blogs = $model->getNew();
        $i=2;
        foreach($blogs as $v)
        {
            $sheet->setCellValue('A'.$i, $v['title']);
            $sheet->setCellValue('B'.$i, $v['content']);
            $sheet->setCellValue('C'.$i, $v['created_at']);
            $sheet->setCellValue('D'.$i, $v['is_show']);
            $i++;
        }
        $date = date('Ymd');
        // 生成 excel 文件
        $writer = new Xlsx($spreadsheet);
        // var_dump($writer);die;
        // var_dump(ROOT . 'excel/'.$date.'.xlsx'); die;

        $writer->save(ROOT . 'excel/'.$date.'.xlsx');
        // 调用 header 函数设置协议头，告诉浏览器开始下载文件

        // 下载文件路径
        $file = ROOT . 'excel/'.$date.'.xlsx';
        // 下载时文件名
        $fileName = '最新的20条日志-'.$date.'.xlsx';

        // 告诉浏览器这是一个二进程文件流    
        Header ( "Content-Type: application/octet-stream" ); 
        // 请求范围的度量单位  
        Header ( "Accept-Ranges: bytes" );  
        // 告诉浏览器文件尺寸    
        Header ( "Accept-Length: " . filesize ( $file ) );  
        // 开始下载，下载时的文件名
        Header ( "Content-Disposition: attachment; filename=" . $fileName );    

        // 读取服务器上的一个文件并以文件流的形式输出给浏览器
        readfile($file);
    }

    // 点赞
    public function zan(){
        $id = $_GET['id'];
    
        if(!isset($_SESSION['id'])){
            echo json_encode([
                'zan' => '403',
                'message' => '登录后才能赞哦！！'
            ]);
            exit;
        }
        $blog = new Blog;
        $zan = $blog->zan($id);
        if($zan){
            json_encode([
                'zan' => '200',
                'message' => '点赞成功'
            ]);
        }else {
            json_encode([
                'zan' => '403',
                'message' => '已经赞过了'
            ]);
        }
    }
    // 点赞列表
    public function zan_list(){
        $id = $_GET['id'];
        $blog = new \models\Blog;
        $data = $blog->zan_list($id);

        echo json_encode([
            'zan' => 200,
            'data' => $data,
        ]);

    }


     // 为所有的日志生成详情页
     public function content_to_html(){
         $blog = new Blog;
         $blog->content2html();
    }

     // 生成index静态页
     public function index_to_html(){
        $blog = new Blog;
        $blog->index2html();
    }

    // 操作Redis
    public function display(){
        // 接收日志 id
        $id = (int)$_GET['id'];

        $blog = new Blog;
        $display = $blog->getDisplay($id);
        echo json_encode([
            'display'=>$display,
            'email'=>isset($_SESSION['email']) ? $_SESSION['email'] : ''
        ]);
    }

    // 定期同步浏览量
    public function getDisplayDb(){

        $blog = new Blog;
        $blog->getDisplayDb();

    }

    // 发表日志 
    public function create(){
        if($_SESSION['emain']){
            echo "<script>alert('你还未登录')</script>";
            redirect('/user/login');
        }
        view('blogs.create');
    }

    public function add_blog(){
        $title = $_POST['title'];
        $content = $_POST['content'];
        $is_show = $_POST['is_show'];

        $blog = new Blog;
        $id = $blog->addBlog($title,$content,$is_show);

        if($id){
            if($is_show==1){
                $blog->addHtml($id);
            }
            message('发表成功',2,'/blog/index');

        }else {
            die('发表失败');
        }

    }
    
    // 删除日志
    public function delete(){
        $id = $_POST['id'];
        $blog = new Blog;
        $blog->delete($id);
        $blog->deleteHtml($id);
        message('删除成功',2,'/blog/index');
    }

    // 修改日志 显示页面
    public function change(){
        $id = $_GET['id'];
        $blog = new Blog;
        $change_g = $blog->change($id);
        view('blogs.change',$change_g);
    }
    // 修改日志
    public function update(){
        $id = $_POST['id'];
        $title = $_POST['title'];
        $content = $_POST['content'];
        $is_show = $_POST['is_show'];
        $blog = new Blog;
        $blog->update($title,$content,$is_show,$id);
        if($is_show==1){
            $blog->addHtml($id);
        }
        message('修改成功！', 0, '/blog/index');
    }

    // 显示私有页面
    public function content(){
        $id = $_GET['id'];
        $blogs = new Blog;
        $blog = $blogs->find($id);
        // var_dump($blog,$blogs);die;

        if($_SESSION['id'] != $blog['user_id']){
            die('你无权访问、这是私人日志');
        }
        view('blogs.content',[
            'blog'=>$blog
        ]);
    }



}