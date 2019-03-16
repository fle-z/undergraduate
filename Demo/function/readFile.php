<?php
$file = 'AprioriData.txt';
$content = file_get_contents($file);
//echo $content;

$array = explode("\r\n", $content);
//var_dump($array);

$data = array();
for($i = 0; $i < count($array); $i++){
    $data[$i] = explode(",", $array[$i]);
}
//var_dump($data);
