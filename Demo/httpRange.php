<?php
$file = __DIR__.'numbers.txt';
$content_type = 'text/plain';

if(($filelength = filesize($file)) === false) {
    error_log("Problem reading filesize of $file")
}

//解析首部来确定发送响应所需的时间
if(isset($_SERVER['HTTP_RANGE'])) {
    //定界符不区分大小写
    if(!preg_match('/bytes=\d*-\d*(,\d*-\d*)*$/i', $_SERVER['HTTP_RANGE'])) {
        error_log("Client requested invalid Range");
        send_error($filelength);
        exit;
    }

    /*规范：客户在一个请求中请求多个字节范围(byte-ranges)时，
    服务器应当按他们在请求中出现的顺序返回这些范围。*/
    $ranges = explode(',', substr($_SERVER['HTTP_RANGE'], 6));
    $offset = array();
    //抽取和验证每个部分，只保存通过验证的部分
    foreach ($ranges as $range) {
        $offset = parse_offset($range, $filelength);
        if($offset !== false){
            $offsets[] = $offset;
        }
    }

    //取决于所请求的合法范围的个数，必须采用不同的格式返回响应
    switch(count($offsets)){
        case 0:
            error_log("Client requested no valid ranges");
            send_error($filelength);
            exit;
            break;
        case 1:
            http_response_code(206);
            list($start, $end);
            header("Content-Range: bytes $start-$end/$filelength");
            header("Content-Type: $content_type");

            $content_length = $end - $start +1;
            $boundaries = array(0 => '', 1 => '');
            break;
        default:
            http_response_code(206);
            $boundary = str_rand(32);

            /*需要计算整个响应的内容长度(Content_length),不过将整个响应加载到一个字符串中会占用
            大量内存，所以使用偏移量计算值。另外利用这个机会计算边界。*/
            $boundaries = array();
            $content_length = 0;

            foreach($offsets as $offset){
                list($start, $end) = $offset;

                $boundary_header = "\r\n".
                                   "--$boundary\r\n".
                                   "Content-Type: $content_type\r\n".
                                   "Content-Range: bytes $start-$end/$filelength\r\n".
                                   "\r\n";
                $content_length += strlen($boundary_header) + ($end - $start + 1);
                $boundaries[] = $boundary_header;
            }
            //增加结束边界
            $boundary_header = "\r\n--$boundary--";
            $content_length += strlen($boundary_header);
            $boundaries[] = $boundary_header;
            //去除第一个边界中多余的\r\n
            $boundaries[0] = substr($boundaries, 2);
            $content_length -= 2;

            //
            $content_type = "multipart/byteranges; boundary=$boundary";
    }
}else{
    $start = 0;
    $length = $filelength - 1;
    $offset = array($start, $end);
    $offsets = array($offset);

    $content_length = $filelength;
    $boundaries = array(0 => '', 1 => '');
}

header("Content-Type: $content_type");
header("Content-Length: $filelength");

$handle = fopen($file, r);
if($handle){
    $offset_count = count($offsets);
    for($i = 0; $i < $offset_count; ++$i){
        print $boundaries[$i];
        list($start, $end) = offsets[$i];
        send_range($handle, $start, $end);
    }
    print $boundaries[$i];
    fclose($handle);
}

function send_range($handle, $start, $end){
    $line_length = 4096;    //魔法数
    if(fseek($handle, $start) === -1){
        error_log("Error:fseek() fail.");
    }
    $left_to_read = $end - $start + 1;
    do {
        $length = min($line_length, $left_to_read);
        if(($buffer = fread($handle, $length)) !== false){
            print $buffer;
        } else {
            error_log("Error:fread() fail.")
        }
    }while($left_to_read -= $length);
}

function send_error(){
    http_response_code(416);
    header("Content-Range: bytes */$filelength");   //响应码416要求发送这个首部
}

function parse_offset($range, $filelength){
    list($start, $end) = explode('-', $range);
    if($start === ''){
        if($end === '' || $end === 0){
            return false;
        }else{
            $start = max(0, $filelength - $end);
            $end = $filelength - 1;
        }
    }else{
        if($end === '' || $end > $filelength - 1){
            $end = $filelength - 1;
        }
        if($start > $end){
            return false;
        }
    }

    return array($start, $end);
}

//生成一个随机字符串来分隔响应中的各个部分
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
