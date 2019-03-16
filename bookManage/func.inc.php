<?php
    include "fileupload.class.php";
    include "image.class.php"
    
    function upload() {
        $path = "./uploads/";                   //设置文件上传后保存的路径
        $up = new FileUpLoad($path);
        if($up->upload('pic')) {
            $filename = $up->getFileame();
            $img = new Image($path);
            $img -> thumb($filename, 300, 300, "");
            $img -> thumb($filename, 80, 80, "");
            $img -> watermark($filename, "logo.gif", 5, "");
            
            return array(true, $filename);        
        } else {
            return array(false ,$up->getErrorMsg());
        }
        
        function delpic($picname){
            $path = "./upload/";
            @unlink($path.$picname);
            @unlink($path.'icon_'.picname);
        }
    }