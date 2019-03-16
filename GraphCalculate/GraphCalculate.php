<html>
	<head>
		<title>图形计算（使用面向对象技术开发）</title>
		<meta http-equiv="Content-type" content="text/html; charset=utf-8">
	</head>
	
	<body>
		<center>
			<h1>图形（周长&面积）计算器</h1>
			
			<a href="GraphCalculate.php? action=rect">矩形</a> ||
			<a href="GraphCalculate.php? action=triangle">三角形</a> ||
			<a href="GraphCalculate.php? action=circle">圆形</a>
		</center>
		<?php
		    error_reporting(E_ALL & ~E_NOTICE);
			
			function __autoload($className){
				include strtolower($className).".class.php";
			}
			
			echo new Form("GraphCalculate.php");
			
			if(isset($_POST["sub"])){
				echo new Result();
			}
		?>
	</body>
</html>