<?php
namespace libs;

class Uploader {

    private static $up = null;
    private function __construct(){}
    private function __clone(){}

    public static function make(){
        if(self::$up === null){
            self::$up = new self;
        }
        return self::$up;
    }

    // 定义属性
    private $root = ROOT.'public/uploads/';
    private $ext = ['image/jpeg','image/jpg','image/png','image/gif','image/mbp'];
    private $maxSize = 1024*1024*1.8;
    private $file;
    private $subdir;

    // $name 上传文件的名字
    // $subdir 保存到2级目录
    public function upload($name,$subdir){
        $this->file = $_FILES[$name];
        $this->subdir = $subdir;

        if(!$this->_checkType()){
            die('图片类型不正确、请重新上传图片');
        }
        if(!$this->_checkSize()){
            die('图片尺寸太大、请重新上传图片');
        }
        $dir = $this->fileDir();
        
        $name = $this->fileName();

        move_uploaded_file($this->file['tmp_name'], $this->root.$dir.$name);
    }


    private function fileDir(){
        $dir = $this->subdir.'/'.date('Ymd');
        if(!is_dir($this->root.$dir)){
            mkdir($this->root.$dir,0777,true);
        }
        return $dir.'/';
    }

    private function fileName(){
        $name = md5(time().rand(1,9999));
        $ext = strrchr($this->file['name'],'.');
        return $name.$ext;
    }

    private function _checkType()
    {
        return in_array($this->file['type'], $this->ext);
    }

    private function _checkSize()
    {
        return $this->file['size'] < $this->maxSize;
    }

}