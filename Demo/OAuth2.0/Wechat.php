<?php
    $url = urlencode("http://localhost/Demo/OAuth2.0/Wechat.php");
    // 公众号的id和secret
     $appid = 'wxeac48b26f2902b2d';
     $appsecret = '1779a55e61a977a6fd2267ccea520458';
     session_start();

     // 获取code码，用于和微信服务器申请token。 注：依据OAuth2.0要求，此处授权登录需要用户端操作
     if(!isset($_GET['code']) && !isset($_SESSION['code'])){
          echo '<a href="https://open.weixin.qq.com/connect/oauth2/authorize?appid='.$appid.'&redirect_uri='.$url.'&response_type=code&scope=snsapi_userinfo&state=1#wechat_redirect">
                    <font style="font-size:30">授权</font>
                </a >';
          exit;
       }

       // 依据code码去获取openid和access_token，自己的后台服务器直接向微信服务器申请即可
       if (isset($_GET['code']) && !isset($_SESSION['token'])){
            $_SESSION['code'] = $_GET['code'];
            $url="https://api.weixin.qq.com/sns/oauth2/access_token?appid=".$appid.
               "&secret=".$appsecret."&code=".$_GET['code']."&grant_type=authorization_code";
            $res = https_request($url);
            $res=(json_decode($res, true));
            $_SESSION['token'] = $res;
           }
           print_r($_SESSION);

           // 依据申请到的access_token和openid，申请Userinfo信息。
           if (isset($_SESSION['token']['access_token'])){
               $url = "https://api.weixin.qq.com/sns/userinfo?access_token=".$_SESSION['token']['access_token']."&openid=".$_SESSION['token']['openid']."&lang=zh_CN";
               echo $url;
               $res = https_request($url);
               $res = json_decode($res, true);
               $_SESSION['userinfo'] = $res;
           }
           print_r($_SESSION);

           // cURL函数简单封装
           function https_request($url, $data = null) {
               $curl = curl_init();
               curl_setopt($curl, CURLOPT_URL, $url);
               curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
               curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
               if (!empty($data)){
                   curl_setopt($curl, CURLOPT_POST, 1);
                   curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
               }
               curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
               $output = curl_exec($curl);
               curl_close($curl);
               return $output;
           }
