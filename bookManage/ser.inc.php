<h3>搜索图书</h3>
<form action = "index.php? action = list" method = "POST">
    图书名称：<input type = "text" name = "bookname" value = "" /><br>
    出版商名：<input type = "text" name = "publisher" value = "" /><br>
    图书作者：<input type = "text" name = "author" value = "" /><br>    
    图书价格：<input type = "text" name = "startprice" size = "5" value = "" /> --
             <input type = "text" name = "endprice" size = "5" value = "" /><br>
    <input type = "submit" name = "add" value = "搜索图书" /> <br>
</form>