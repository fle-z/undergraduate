<?php
function __initialize(){
    header('Content-Type:text/html;charset=utf-8'); //utf-8编码
}

//安装PSR-0兼容的类自动加载工具
spl_autoload_register(function($class){
    require preg_replace('{\\\\|_(?!.*\\\\)}', DIRECTORY_SEPARATOR,
            trim($class, '\\')).'.php';
});

//使用Markdown表示类Wiki的文本标记
//位于http://michelf.ca/projects/php-markdown/
use Michelf\Markdown;

//存储wiki页面的目录
//确保web服务器用户可以写这个目录
define('PAGEDIR', dirname(__FILE__).'/pages');

//得到页面名，或使用默认文件名
$page = isset($_GET['page'])? $_GET['page'] : 'Home';

//显示所请求的编辑表单
if(isset($_GET['edit'])) {
    pageHeader($page);
    edit($page);
    pageFooter($page, false);
}else if(isset($_POST['edit'])){   //保存所提交的表单
    file_put_contents(pageToFile($_POST['page']), $_POST['contents']);
    header('Location:http://'.$_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME'].
            '?page='.urlencode($_POST['page']));
    exit();
}else{  //显示一个页面
    pageHeader($page);
    //如果这个页面存在，显示该页面，并在页脚显示一个Edit链接
    if(is_readable(pageToFile($page))){
        $text = file_get_contents(pageToFile($page));
        //转换Markdown语法（使用上面加载的Markdown库）
        $text = Markdown::defaultTransform($text);
        //使【links】链接到其他wiki页面
        $text = wikiLinks($text);
        echo $text;
        pageFooter($page, true);
    }else{
        edit($page, true);
        pageFooter($page, false);
    }
}

function pageHeader($page){ ?>
<html>
    <head>
        <title>Wiki:<?php echo htmlentities($page) ?></title>
    </head>
    <body>
        <h1> <?php echo htmlentities($page) ?> </h1>
        <hr/>
<?php
}

function pageFooter($page, $displayEditLink){
    $timestamp = @filemtime(pageToFile($page));  //取得文件的修改时间
    if($timestamp){
        $lastModified = strftime('%c', $timestamp); //根据区域设置格式化本地时间／日期
    }else{
        $lastModified = 'Never';
    }
    if($displayEditLink){
        $editLink = '- <a href="?page='.urlencode($page).'&edit=true">Edit</a>';
    }else{
        $editLink = '';
    }
?>
<hr/>
<em>Last Modified: <?php echo $lastModified ?></em>
<?php echo $editLink ?> - <a href="<?php echo $_SERVER['SCRIPT_NAME'] ?>">Home</a>
</body>
</html>
<?php
}

//显示一个编辑表单，如果页面存在，则展示该内容
function edit($page, $isNew = false){
    if($isNew){
        $contents = '';
    ?>
    <p><b>This page doesn't exit yet.</b> To creatit, enter its contents below
        and click the <b>Save</b> button.</p>
    <?php
    }else{
        $contents = file_get_contents(pageToFile($page));
    }
    ?>
    <form method = 'post' action = '<?php echo htmlentities($_SERVER['SCRIPT_NAME']) ?>'>
        <input type = 'hidden' name = 'edit' value = 'true'/>
        <input type = 'hidden' name = 'page' value = '<?php echo htmlentities($page)?>'/>
        <textarea name = 'contents' rows = '20' cols = '60'>
            <?php echo htmlentities($contents, ENT_COMPAT,'UTF-8') ?>
        </textarea>
        <br/>
        <input type = 'submit' value = 'Save'>
    </from>
    <?php
}

//将提交的页面转换为一个文件名。这里使用md5()避免$page中的字符带来安全问题
function pageToFile($page){
    return PAGEDIR.'/'.md5($page).".txt";
}

//将页面中诸如[something]的文本转换为一个HTML链接
function wikiLinks($page){
    if(preg_match_all('/\[([^\]]+?)\]/', $page, $matches, PREG_SET_ORDER)){
        foreach ($matches as $match) {
            $page = str_replace($match[0], '<a href="'.$_SERVER['SCRIPT_NAME'].
                '?page='.urlencode($match[1]).'">'.htmlentities($match[1]).'</a>', $page);
        }
    }
    return $page;
}
