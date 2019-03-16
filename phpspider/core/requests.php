<?php

/**
 * phpspider - A PHP Framework For Crawler
 *
 * @package  requests
 * @author   Seatle Yang <seatle@foxmail.com>
 */

class requests
{
    /**
     * 版本号
     * @var string
     */
    const VERSION = '1.10.0';

    protected static $ch = null;
    protected static $timeout = 10;
    //protected static $request = array(
        //'headers' => array()
    //);
    protected static $cookies = array();
    protected static $domain_cookies = array();
    protected static $hosts = array();
    public static $headers = array();
    public static $proxies = array();
    public static $url = null;
    public static $domain = null;
    public static $raw = null;
    public static $content = null;
    public static $encoding = 'utf-8';
    public static $info = array();
    public static $status_code = 0;
    public static $error = null;

    /**
     * set timeout
     *
     * @param init $timeout
     * @return
     */
    public static function set_timeout($timeout)
    {
        self::$timeout = $timeout;
    }

    /**
     * 设置代理
     * 
     * @param mixed $proxies
     * array (
     *    'http': 'socks5://user:pass@host:port',
     *    'https': 'socks5://user:pass@host:port'
     *)
     * @return void
     * @author seatle <seatle@foxmail.com> 
     * @created time :2016-09-18 10:17
     */
    public static function set_proxies($proxies)
    {
        self::$proxies = $proxies;
    }

    /**
     * 设置Headers
     *
     * @param string $headers
     * @return void
     */
    public static function add_header($key, $value)
    {
        self::$headers[$key] = $value;
    }

    /**
     * 设置COOKIE
     *
     * @param string $cookie
     * @return void
     */
    public static function add_cookie($key, $value, $domain = '')
    {
        if (empty($key) || empty($value)) 
        {
            return false;
        }
        if (!empty($domain)) 
        {
            self::$domain_cookies[$domain][$key] = $value;
        }
        else 
        {
            self::$cookies[$key] = $value;
        }
        return true;
    }

    public static function add_cookies($cookies, $domain = '')
    {
        $cookies_arr = explode(";", $cookies);
        if (empty($cookies_arr)) 
        {
            return false;
        }

        foreach ($cookies_arr as $cookie) 
        {
            $cookie_arr = explode("=", $cookie);
            $key = $value = "";
            foreach ($cookie_arr as $k=>$v) 
            {
                if ($k == 0) 
                {
                    $key = trim($v);
                }
                else 
                {
                    $value .= trim(str_replace('"', '', $v));
                }
            }

            if (!empty($domain)) 
            {
                self::$domain_cookies[$domain][$key] = $value;
            }
            else 
            {
                self::$cookies[$key] = $value;
            }
        }
        return true;
    }

    public static function get_cookie($name, $domain = '')
    {
        if (!empty($domain) && !isset(self::$domain_cookies[$domain])) 
        {
            return '';
        }
        $cookies = empty($domain) ? self::$cookies : self::$domain_cookies[$domain];
        return isset($cookies[$name]) ? $cookies[$name] : '';
    }
    
    public static function get_cookies($domain = '')
    {
        if (!empty($domain) && !isset(self::$domain_cookies[$domain])) 
        {
            return array();
        }
        return empty($domain) ? self::$cookies : self::$domain_cookies[$domain];
    }

    /**
     * 设置 user_agent
     *
     * @param string $useragent
     * @return void
     */
    public static function set_useragent($useragent)
    {
        self::$headers['User-Agent'] = $useragent;
    }

    /**
     * set referer
     *
     */
    public static function set_referer($referer)
    {
        self::$headers['Referer'] = $referer;
    }

    /**
     * 设置伪造IP
     *
     * @param string $ip
     * @return void
     */
    public static function set_client_ip($ip)
    {
        self::$headers["CLIENT-IP"] = $ip;
        self::$headers["X-FORWARDED-FOR"] = $ip;
    }

    /**
     * 设置Hosts
     *
     * @param string $hosts
     * @return void
     */
    public static function set_hosts($hosts)
    {
        self::$hosts = $hosts;
    }

    public static function get_response_body($domain)
    {
        $headers = array();
        $body = '';
        // 解析HTTP数据流
        if (!empty(self::$raw)) 
        {
            self::get_response_cookies($domain);
            // body里面可能有 \r\n\r\n，但是第一个一定是HTTP Header，去掉后剩下的就是body
            $array = explode("\r\n\r\n", self::$raw);
            foreach ($array as $k=>$v) 
            {
                // post 方法会有两个http header：HTTP/1.1 100 Continue、HTTP/1.1 200 OK
                if (preg_match("#^HTTP/.*? 100 Continue#", $v)) 
                {
                    unset($array[$k]);
                }
                elseif (preg_match("#^HTTP/.*? 200 OK#", $v)) 
                {
                    unset($array[$k]);
                    self::get_response_headers($v);
                }
            }
            $body = implode("\r\n\r\n", $array);
        }

        return $body;
    }

    public static function get_response_cookies($domain)
    {
        // 解析Cookie并存入 self::$cookies 方便调用
        preg_match_all("/.*?Set\-Cookie: ([^\r\n]*)/i", self::$raw, $matches);
        $cookies = empty($matches[1]) ? array() : $matches[1];

        // 解析到Cookie
        if (!empty($cookies)) 
        {
            $cookies = implode(";", $cookies);
            $cookies = explode(";", $cookies);
            foreach ($cookies as $cookie) 
            {
                $cookie_arr = explode("=", $cookie);
                // 过滤 httponly、secure
                if (count($cookie_arr) < 2) 
                {
                    continue;
                }
                $cookie_name = !empty($cookie_arr[0]) ? trim($cookie_arr[0]) : '';
                if (empty($cookie_name)) 
                {
                    continue;
                }
                // 过滤掉domain路径
                if (in_array(strtolower($cookie_name), array('path', 'domain', 'expires', 'max-age'))) 
                {
                    continue;
                }
                self::$domain_cookies[$domain][trim($cookie_arr[0])] = trim($cookie_arr[1]);
            }
        }
    }

    public static function get_response_headers($html)
    {
        $header_lines = explode("\n", $html);
        if (!empty($header_lines)) 
        {
            foreach ($header_lines as $line) 
            {
                $header_arr = explode(":", $line);
                $key = empty($header_arr[0]) ? '' : trim($header_arr[0]);
                $val = empty($header_arr[1]) ? '' : trim($header_arr[1]);
                if (empty($key) || empty($val)) 
                {
                    continue;
                }
                if (strtolower($key) == 'content-type') 
                {
                    self::get_response_encoding($val);
                }
                $headers[$key] = $val;
            }
        }
    }

    public static function get_response_encoding($html)
    {
        $charset_arr = explode('charset=', $html);
        if (!empty($charset_arr[1])) 
        {
            self::$encoding = strtolower(trim($charset_arr[1]));
        }
    }

    /**
     * 初始化 CURL
     *
     */
    public static function init()
    {
        if (!is_resource ( self::$ch ))
        {
            self::$ch = curl_init ();
            curl_setopt( self::$ch, CURLOPT_RETURNTRANSFER, true );
            curl_setopt( self::$ch, CURLOPT_CONNECTTIMEOUT, self::$timeout );
            curl_setopt( self::$ch, CURLOPT_HEADER, false );
            curl_setopt( self::$ch, CURLOPT_USERAGENT, "phpspider-requests/".self::VERSION );
            curl_setopt( self::$ch, CURLOPT_TIMEOUT, self::$timeout + 5);
            // 在多线程处理场景下使用超时选项时，会忽略signals对应的处理函数，但是无耐的是还有小概率的crash情况发生
            curl_setopt( self::$ch, CURLOPT_NOSIGNAL, true);
        }
        return self::$ch;
    }

    /**
     * get
     *
     *
     */
    public static function get($url, $fields = array())
    {
        self::init ();
        return self::http_client($url, 'get', $fields);
    }

    /**
     * $fields 有三种类型:1、数组；2、http query；3、json
     * 1、array('name'=>'yangzetao') 2、http_build_query(array('name'=>'yangzetao')) 3、json_encode(array('name'=>'yangzetao'))
     * 前两种是普通的post，可以用$_POST方式获取
     * 第三种是post stream( json rpc，其实就是webservice )，虽然是post方式，但是只能用流方式 http://input 后者 $HTTP_RAW_POST_DATA 获取 
     * 
     * @param mixed $url 
     * @param array $fields 
     * @param mixed $proxies 
     * @static
     * @access public
     * @return void
     */
    public static function post($url, $fields = array())
    {
        self::init ();
        return self::http_client($url, 'post', $fields);
    }

    public static function put($url, $fields = array())
    {
    }

    public static function delete($url, $fields = array())
    {
    }

    public static function head($url, $fields = array())
    {
    }

    public static function options($url, $fields = array())
    {
    }

    public static function http_client($url, $type = 'get', $fields)
    {
        $pattern = "/\b(([\w-]+:\/\/?|www[.])[^\s()<>]+(?:\([\w\d]+\)|([^[:punct:]\s]|\/)))/";
        if(!preg_match($pattern, $url))
        {
            self::$error = "You have requested URL ({$url}) is not a valid HTTP address";
            return false;
        }

        // 如果是 get 方式，直接拼凑一个 url 出来
        if (strtolower($type) == 'get' && !empty($fields)) 
        {
            $url = $url . (strpos($url,"?")===false ? "?" : "&") . http_build_query($fields);
        }

        $parse_url = parse_url($url);
        if (empty($parse_url) || empty($parse_url['host']) || !in_array($parse_url['scheme'], array('http', 'https'))) 
        {
            self::$error = "No connection adapters were found for '{$url}'";
            return false;
        }
        $scheme = $parse_url['scheme'];
        $domain = $parse_url['host'];

        // 随机绑定 hosts，做负载均衡
        //if (self::$hosts) 
        //{
            //$host = $parse_url['host'];
            //$key = rand(0, count(self::$hosts)-1);
            //$ip = self::$hosts[$key];
            //$url = str_replace($host, $ip, $url);
            //self::$headers['Host'] = $host;
        //}

        curl_setopt( self::$ch, CURLOPT_URL, $url );
        //curl_setopt( self::$ch, CURLOPT_REFERER, "http://www.baidu.com" );

        // 如果是 post 方式
        if (strtolower($type) == 'post')
        {
            curl_setopt( self::$ch, CURLOPT_POST, true );
            curl_setopt( self::$ch, CURLOPT_POSTFIELDS, $fields );
        }

        $cookies = self::get_cookies();
        $domain_cookies = self::get_cookies($domain);
        $cookies =  array_merge($cookies, $domain_cookies);

        // 是否设置了cookie
        if (!empty($cookies)) 
        {
            foreach ($cookies as $key=>$value) 
            {
                $cookie_arr[] = $key."=".$value;
            }
            $cookies = implode("; ", $cookie_arr);
            curl_setopt( self::$ch, CURLOPT_COOKIE, $cookies );
        }

        if (self::$headers)
        {
            $headers = array();
            foreach (self::$headers as $k=>$v) 
            {
                $headers[] = $k.": ".$v;
            }
            curl_setopt( self::$ch, CURLOPT_HTTPHEADER, $headers );
        }

        //curl_setopt( self::$ch, CURLOPT_ENCODING, 'gzip' );

        if (self::$proxies)
        {
            if (!empty(self::$proxies[$scheme])) 
            {
                curl_setopt( self::$ch, CURLOPT_PROXY, self::$proxies[$scheme] );
            }
        }

        //curl_setopt( self::$ch, CURLOPT_USERAGENT, "fsfsf" );

        // header + body，header 里面有 cookie
        curl_setopt( self::$ch, CURLOPT_HEADER, true );

        self::$raw = curl_exec ( self::$ch );
        //var_dump($data);
        self::$info = curl_getinfo( self::$ch );
        self::$status_code = self::$info['http_code'];
        if (self::$raw === false)
        {
            self::$error = ' Curl error: ' . curl_error( self::$ch );
        }

        // 关闭句柄
        curl_close( self::$ch );

        // 请求成功之后才把URL存起来
        self::$url = $url;
        self::$content = self::get_response_body($domain);
        //$data = substr($data, 10);
        //$data = gzinflate($data);
        return self::$content;
    }

}
