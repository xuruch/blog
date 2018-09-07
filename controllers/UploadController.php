<?php 
namespace controllers;

class UploadController {

    public function upload(){
        $file = $_FILES['image'];

        $name = time();
        move_uploaded_file($file['tmp_name'],ROOT.'/public/uploads/'.$name.'.png');

        echo json_encode([
            'success'=>true,
            'file_path'=>'/public/uploads/'.$name.'.png'
        ]);
    }

}