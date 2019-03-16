<?php
//alert提示
function alert($msg){
    echo "<script>alert('$msg');</script>";
}

//生成一个随机字符串
function str_rand($length = 32,
$characters = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ") {
    if (!is_int($length) || $length < 0) {
        return false;
    }
    $characters_length = strlen($characters) - 1;
    $string = '';
    for($i = $length; $i > 0; --$i){
        $string .= $characters[mt_rand(0, $characters_length)];
    }

    return $string;
}

//获取服务器IP地址
function getIp(){
    if($_SERVER["HTTP_X_FORWARDED_FOR"]) {
        $ip = $_SERVER["HTTP_X_FORWARDED_FOR"];
    } else if($_SERVER["HTTP_CELENT_IP"]) {
        $ip = $_SERVER["HTTP_CELENT_IP"];
    } else if($_SERVER["REMOTE_ADDR"]) {
        $ip = $_SERVER["REMOTE_ADDR"];
    } else if (getenv("HTTP_X_FORWARDED_FOR")) {
        $ip = $_SERVER["HTTP_X_FORWARDED_FOR"];
    } else if(getenv("HTTP_CELENT_IP")) {
        $ip = $_SERVER["HTTP_CELENT_IP"];
    } else if(getenv("REMOTE_ADDR")) {
        $ip = $_SERVER["REMOTE_ADDR"];
    } else {
        $ip = "Unknow";
    }

    return $ip;
}

//页面跳转
function url_redirect($url, $delay=''){
    if($delay = ''){
        echo "<script>window.location.href = '$url'</script>";
    } else {
        "<meta http-equiv='refresh' content='$delay;Url=$url'/>";
    }
}

//判断用户是否是移动设备
function bool isMobile(){
    //如果有HTTP_X_WAP_PROFILE则一定是移动设备
    if(isset($_SERVER("HTTP_X_WAP_PROFILE"))) {
        return true;
    }
    //如果via信息含有wap则一定是移动设备，部分服务商会屏蔽该信息
    if(isset($_SERVER["HTTP_VIA"])) {
        return stristr($_SERVER['HTTP_VIA'], "wap") ? true : false;
    }
    //判断手机发送的客户端标志，如：查找手机浏览器的关键字
    //协议法，只支持wml不支持html，一定是移动设备；vml在html之前也是移动设备
}
 ?>
