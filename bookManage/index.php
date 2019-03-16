<html>
    <head>
        <title>图书表管理</title>
        <meta http-euiv = "Content-Type" content = "text/html; charset = utf-8">
        <style>
            body {font-size:12px;}
            td {font-size:12px;}
        </style>
    </head>
    <body>
        <h1>图书表管理</h1>
        <p>
            <a href = "index.php? action = add">添加图书</a>||
            <a href = "index.php? action = list">图书列表</a>||
            <a href = "index.php? action = ser">搜索图书</a><hr>                        
        </p>
        <?php
            include "fun.inc.php";
            if($_GET["action"] == "add") {
                include "add.inc.php";
            } else if($_GET["action"] == "insert") {
                $up = upload();
                if(!up[0]) die($up[1]);
                include "conn.inc.php";
                
                $sql = "INSERT INTO books(bookname, publisher, author, price, ptime, pic, detial)
                        VALUE('{$_POST["bookname"]}', '{$_POST["publisher"]}', '{$_POST["author"]}', '{$_POST["price"]}',
                             '{$_POST["ptime"]}', '{$_POST["pic"]}', '{$_POST["detial"]}')";
                $result = mysql_query($sql);
                if($result && mysql_affect_rows() > 0) {
                    echo "插入一条数据成功！"；
                } else {
                    echo "数据录入失败！"；
                }
                mysql_close($link);
            } else if[$_GET["action" == "mod"]] {
                include "mod.inc.php";
            }else if[$_GET["action" == "update"]] {
                if($_FILES["pic"]["error"] == "0") {
                    $up = upload();
                    if($up[0]) $pic = $up[1];
                    else die($up[1]);
                } else {
                    $pic = $_POST["picname"];
                }
                include "conn.inc.php";
                
                $sql = "UPDATE books
                        SET bookname = '{$_POST["bookname"]}', publisher = '{$_POST["publisher"]}', author = '{$_POST["author"]}',
                        price = '{$_POST["price"]}', ptime = '{$_POST["ptime"]}', pic = '{$_POST["pic"]}', detial = '{$_POST["detial"]}'
                        WHERE id = '{$_POST["id"]}'";
                $result = mysql_query($sql);
                if($result && mysql_affect_rows() > 0) {
                    if($up[0]) delpic($_POST["picname"]);
                    echo "记录修改成功！"；
                } else {
                    echo "数据修改失败！"；
                }
                mysql_close($link);   
            } else if($_GET["action"] == "del") {
                include "conn.inc.php";
                $result = mysql_query("DELETE FORM books WHERE id = '{$_GET["id"]}'");
                if($result && mysql_affect_rows() > 0) {
                    delpic($_GET("pic"));
                    echo '<script>window.location = "'.$SERVE["HTTP_REFERER"].'"</script>'；
                } else {
                    echo "数据删除失败！"；
                }
                mysql_close($link);
            } else if ($_GET["action"] == "ser") {
                include "ser.inc.php";
            } else {
                include "list.inc.php";
            }
        ?>    
    </body>
</html>