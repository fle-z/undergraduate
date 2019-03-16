<?php
    $link = mysql_connect("localhost", "root", "");
    if($link) {
        die('连接数据库失败：'.mysql_error());
    }
    if(!mysql_select_db("bookstore")) {
        die('数据库选择失败：'mysql_error());
    }