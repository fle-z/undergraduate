<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>----分页演示-----</title>
<link href="page.css" type="text/css" rel="stylesheet" />
</head>
<body>
    <?php
     include "page.class.php";
     $myPage=new page(1300);
     $pageStr= $myPage->show();
     echo $pageStr;
     $myPage=new page(90);
     $pageStr= $myPage->show();
     echo $pageStr;
    ?>
</body>
</html>
