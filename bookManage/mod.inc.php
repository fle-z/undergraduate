<?php
    include "conn.inc.php"
    $sql = "SELECT id, bookname,publisher, author, price, pic, detail, FORM books WHERE id = {$_GET["id"]}";
    $result = mysql_qurey($sql);
    if($result && mysql_num_rows($result) > 0) {
        list ($id, $bookname, $publisher, $author, $price, $pic. $detail) = mysql_fetch_row($result);
    } else {
        die ("没有找到需要修改的图书！")
    }
    
    mysql_fetch_result($result);
    mysql_close($link);
?>

<h3>修改商品：</h3>
<form enctype = "multipart/form-data" action = "index.php? action = update" method = "POST">
    <input type = "hidden" name = "id" value = "<?php echo $id ?>" />
    图书名称：<input type = "text" name = "bookname" value = "<?php echo $bookname ?>" /><br>
    出版商名：<input type = "text" name = "publisher" value = "<?php echo $publisher ?>" /><br>
    图书作者：<input type = "text" name = "price" value = "<?php echo $author ?>" /><br>    
    图书价格：<input type = "text" name = "price" value = "<?php echo $price ?>" /><br>
    <input type = "hidden" name = "MAX_FIFE_SIZE" value = "1000000"/><br>
    <img src = "./upload/icon_<?php echo $pic ?>"><br>
    图书图片：<input type = "file" name = "pic" value = "" /><br>
    图书介绍：<textarea name = "detail" cols = "30" rows = "5"><?php echo $detail ?></textarea><br>
    <input type = "submit" name = "add" value = "修改图书" />
</form>