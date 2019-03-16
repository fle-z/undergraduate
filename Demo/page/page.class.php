<?php
    class page{
        private $total;     //总共记录条数
        private $pageUrl;   //页面的URL地址
        private $pageNum;   //分页的数量
        private $listRows;  //每页的预定行数
        private $firstRow;  //每页第一行的编号
        private $listNum;   //每页的实际行数
        private $page;      //当前所在的页数
        private $html;
        private $config = array(
            'header' => "个记录",
            'prev'   => "上一页",
            'next'   => "下一页",
            'first'  => "首页",
            "last"   => "尾页",
        );

        public function __construct($total, $listRows = 10){
            $this->total    = $total;
            $this->listRows = $listRows;
            $this->page     = !empty($_GET['page']) ? $_GET['page'] : 1;
            $this->pageNum  = ceil($this->total / $this->listRows);
            $this->firstRow = $this->firstLine();
            $this->pageUrl  = $this->getPageUrl();
            $this->islegal();
        }

        private function islegal(){
              if((!is_numeric($this->total)) ||  $this->total < 1){
                  $this->total = 1;
              }elseif($this->page > $this->pageNum){
                  $this->page = $this->pageNum;
              }
          }
        private function getPageUrl(){
            //var_dump($_SERVER);
            $url = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
            $parse = parse_url($url);
            //var_dump($parse);
            if(isset($parse['query'])){
                parse_str($parse['query'], $params);
                unset($params['page']);
                $url = $parse['path'].'?'.http_build_query($params)."&page";
            }else{
                $url = $parse['path']."?&page";
            }

            return $url;
        }

        private function firstLine(){
            if($this->total == 0){
                return 0;
            } else {
                return (($this->page)-1)*($this->listRows) + 1;
            }
        }

        private function listNum(){
            return min(($this->page) * ($this->listRows), $this->total) - $this->firstLine();
        }

        private function first(){
            $html = "";
            if($this->page == 1){
                $html .= "";
            } else {
                $html .= "&nbsp;&nbsp;
                          <a href='{$this->pageUrl}=1' class='tips'>{$this->config["first"]}</a>
                          &nbsp;&nbsp;";

                return $html;
            }
        }

        private function prev(){
            $html = "";
            if($this->page == 1){
                $html .= "";
            } else {
                $html .= "&nbsp;&nbsp;
                          <a href='{$this->pageUrl}=".($this->page - 1)."' class='tips'>{$this->config["prev"]}</a>
                          &nbsp;&nbsp;";

                return $html;
            }
        }

        private function pageList(){
            $str = "";
            $current = "";
            if($this->pageNum <= 10){
                for($i = 1; $i <= $this->pageNum; ++$i){
                    if($i == $this->page){
                        $current = "class='current'";
                    } else {
                        $current = "";
                    }
                     $str .= "<a href='{$this->pageUrl}={$i}' {$current}>$i</a>"."\n" ;
                }
            } else {
                if($this->page < 3){
                    for($i = 1; $i <= 3; ++$i){
                        if($i == $this->page){
                            $current = "class='current'";
                        }else{
                            $current = "";
                        }
                        $str .= "<a href='{$this->pageUrl}={$i}' {$current}>$i</a>"."\n" ;
                    }

                    $str.="<span class=\"dot\">……</span>"."\n";

                    for($i = $this->pageNum-3+1; $i <= $this->pageNum; ++$i){
                         $str .="<a href='{$this->pageUrl}={$i}' >$i</a>"."\n";
                    }
                }else if($this->page <= 5){
                    for($i = 1; $i <= ($this->page+1); ++$i){
                        if($i == $this->page){
                            $current = "class='current'";
                        }else{
                            $current = "";
                        }
                        $str .= "<a href='{$this->pageUrl}={$i}' {$current}>$i</a>"."\n" ;
                    }
                    $str.="<span class=\"dot\">……</span>"."\n";
                    for($i = $this->pageNum-3+1; $i <= $this->pageNum; ++$i){
                         $str .="<a href='{$this->pageUrl}={$i}' >$i</a>"."\n";
                    }
                }else if($this->page > 5 && $this->page <= $this->pageNum - 5){
                    for($i = 1; $i <= 3; ++$i){
                        $str .="<a href='{$this->pageUrl}={$i}' >$i</a>"."\n";
                    }
                    $str.="<span class=\"dot\">……</span>"."\n";
                    for($i = $this->page-1; $i <= ($this->page+1); ++$i){
                        if($i == $this->page){
                            $current = "class='current'";
                        }else{
                            $current = "";
                        }
                        $str .= "<a href='{$this->pageUrl}={$i}' {$current}>$i</a>"."\n" ;
                    }
                    $str.="<span class=\"dot\">……</span>"."\n";
                    for($i = $this->pageNum-3+1; $i <= $this->pageNum; ++$i){
                         $str .="<a href='{$this->pageUrl}={$i}' >$i</a>"."\n";
                    }
                }else{
                    for($i = 1; $i <= 3; ++$i){
                        $str .="<a href='{$this->pageUrl}={$i}' >$i</a>"."\n";
                    }
                    $str.="<span class=\"dot\">……</span>"."\n";
                    for($i = $this->pageNum-5; $i <= $this->pageNum; ++$i){
                        if($i == $this->page){
                            $current = "class='current'";
                        }else{
                            $current = "";
                        }
                        $str .= "<a href='{$this->pageUrl}={$i}' {$current}>$i</a>"."\n" ;
                    }
                }
            }

            return $str;
        }

        private function next(){
            $html = "";
            if($this->page == $this->pageNum){
                $html .= "";
            } else {
                $html .= "&nbsp;&nbsp;
                          <a href='{$this->pageUrl}=".($this->page + 1)."' class='tips'>{$this->config["next"]}</a>
                          &nbsp;&nbsp;";

                return $html;
            }
        }

        private function last(){
            $html = "";
            if($this->page == $this->pageNum){
                $html .= "";
            } else {
                $html .= "&nbsp;&nbsp;
                          <a href='{$this->pageUrl}={$this->pageNum}' class='tips'>{$this->config["last"]}</a>
                          &nbsp;&nbsp;";

                return $html;
            }
        }

        private function goPage(){
            return '&nbsp;&nbsp;
                    <input type="text" onkeydown="
                        javascript:if(event.keyCode==13){
                            var page = (this.value > '.$this->pageNum.')?'.
                            $this->pageNum.' : this.value;
                            location=\''.$this->pageUrl.'=\'+page+\'\'}"
                    value="'.$this->page.'"style="width:30px">

                    <input type="button" value="GO" onclick="
                        javascript:var page = (this.previousSibling.value > '.$this->pageNum.')?'.
                        $this->pageNum.' : this.previousSibling.value;
                        location=\''.$this->pageUrl.'=\'+page+\'\'">
                    &nbsp;&nbsp;';
        }

        function show(){
            $html = "";
            $html .= "&nbsp;&nbsp;
                        共有<b>{$this->total}</b>{$this->config["header"]}
                        &nbsp;&nbsp;";
            $html .= "&nbsp;&nbsp;
                        每页显示<b>".($this->listRows)."</b>条，
                        本页<b>".($this->firstLine())."-".($this->firstLine()+$this->listNum())."</b>条
                        &nbsp;&nbsp;";
            $html .= "&nbsp;&nbsp;
                        <b>{$this->page}/{$this->pageNum}</b>页
                        &nbsp;&nbsp;";
            $html .= "<div class=\"Pagination\">";
            $html .= $this->first();
            $html .= $this->prev();
            $html .= $this->pageList();
            $html .= $this->next();
            $html .= $this->last();
            $html .= $this->goPage();
            $html .= "</div>";

            return $html;
        }
    }
