<?php
$filename = 'online.txt';
$cookieName = '11'; //cookie名称
$onLineTime = 600;  //在线有效时间

$online = file($filename);
//file()函数把整个文件读入一个数组中，与file_get_contents()类似。
//不同的是file()将文件作为一个数组返回，数组中的每个单元都是文件中相应的一行，包括换行符在内。
//如果失败则返回false
$nowTime = $_SERVER['REQUEST_TIME'];
$nowOnline = array();
//得到任然有效的数据
foreach($online as $line){
    $row = explode('|', $line);
    $sessTime = trim($row[1]);
    if(($nowTime - $sessTime) <= $onLineTime){
        $nowOnline[$row[0]] = $sessTime;
    }
}

if(isset($_COOKIE[$cookieName])){
    $uid = $_COOKIE[$cookieName];
}else{
    $vid = 0;
    do{
        $vid++;
        $uid = 'U'.$vid;
    }while(array_key_exists($uid, $nowOnline));
    setcookie($cookieName, $uid);
}
$nowOnline[$uid] = $nowTime;
$total_online_num = count($nowOnline);

if($fp = @fopen($filename, 'w')){
    if(flock($fp, LOCK_EX)){
        rewind($fp);
        foreach($nowOnline as $fuid=>$ftime){
            $fline = $fuid.'|'.$ftime."\n";
            @fputs($fp, $fline);
        }
        flock($fp, LOCK_UN);
        fclose($fp);
    }
}

echo '<script>document.write("'.$total_online_num.'")</script>';
