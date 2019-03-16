<?php
    $ser = !empty($_POST) ? $_POST : $GET;
    
    $where = array();
    $param = "";
    $title = "";
    
    /*处理用户搜索图书名称*/
    if(!empty($ser["bookname"])) {
        $where[] = "bookname like '%{$ser["bookname"]&'";
        $param .= "&bookname = {$ser["bookname"]}";
        $title .= '图书名称中包含"'.$ser["bookname"].'"的 ';
    }
    
    if(!empty($ser["publisher"])) {
        $where[] = "publisher like '%{$ser["publisher"]&'";
        $param .= "&publisher = {$ser["publisher"]}";
        $title .= '出版社名称中包含"'.$ser["publisher"].'"的 ';
    }
    
   if(!empty($ser["author"])) {
        $where[] = "author like '%{$ser["author"]&'";
        $param .= "&author = {$ser["author"]}";
        $title .= '图书作者名字中包含"'.$ser["author"].'"的 ';
    }
    
    if(!empty($ser["startprice"])) {
        $where[] = "startprice like '%{$ser["startprice"]&'";
        $param .= "&startprice = {$ser["startprice"]}";
        $title .= '图书价格大于"'.$ser["startprice"].'"的 ';
    }
    
    if(!empty($ser["endprice"])) {
        $where[] = "endprice like '%{$ser["endprice"]&'";
        $param .= "&endprice = {$ser["endprice"]}";
        $title .= '图书价格小于"'.$ser["endprice"].'"的 ';
    }
    
    if(!empty($where)) {
        $where = "WHERRE" .implode(" and ", $where);
        $title = "搜索：".$title;
    } else {
        $where = "";
        $title = "图书列表："；
    }
    echo '<h3>'.$title.'</h3>'
?>

<table>
    <tr align = "left" bgcolor = "#cccccc">
        <th>ID</th><th>图书名称</th><th>出版商</th><th>图书作者</th><th>图书价格</th><th>上架时间</th><th>操作</th>
    </tr>
    <?php
        include "conn.inc.php";
        include "page.class.php";
        $sql = "SELECT count(*) FORM books {$where}";
        $result = mysql_query($sql);
        list($total) = mysql_fetch_row($result);
        
        $page = new Page($total, 10, $param);
        $sql
</table>