<?php
function curl($url)
{
    $header[] = 'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8';
    $header[] = 'Accept-Encoding: gzip, deflate, sdch, br';
    $header[] = 'Accept-Language: zh-CN,zh;q=0.8';
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (iPhone; CPU iPhone OS 6_0 like Mac OS X) AppleWebKit/536.26 (KHTML, like Gecko) Version/6.0 Mobile/10A5376e Safari/8536.25');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_ENCODING, 'gzip,deflate');
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    $output = curl_exec($ch);
    curl_close($ch);
    return $output;
}

//获取ID
function GetFileID($content, $start, $end)
{
    $r = explode($start, $content);
    if (isset($r[1])) {
        $r = explode($end, $r[1]);
        return $r[0];
    }
    return '';
}

//获取页面信息
//$mode=1 单文件
//$mode=2 多文件
function getInfo($key, $id, $mode)
{
    if ($key === count(urls) - 1) {
        if ($mode === 2) {
            //多文件/单文件均已尝试解析均失败
            $Json = array(
                'code' => 201,
                'msg' => '文件信息获取失败!',
            );
            echo json_encode($Json, JSON_NUMERIC_CHECK | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            die();
        }
        ++$mode;
        return getInfo(0, $id, $mode);
    }
    $info = getURL($key, $id, $mode);
    if (empty($info) || $info === false) {
        ++$key;
        return getInfo($key, $id, $mode);
    }
    $GLOBALS['right'] = urls[$key];
    return $info;
}


//获取链接
function getURL($key, $id, $mode)
{
    //首创支持多文件处理
    if ($mode === 1) {
        $url = urls[$key] . '/tp/' . $id;
    } else {
        $url = urls[$key] . '/' . $id;
    }
    return curl($url);
}

//请求状态
function check_status($info, $pass)
{
    if (strpos($info, '文件取消分享了')) {
        $Json = array(
            'code' => 201,
            'msg' => '文件取消分享了',
        );
    } else if (strpos($info, '文件不存在')) {
        $Json = array(
            'code' => 201,
            'msg' => '文件不存在，或已删除',
        );
    } else if (strpos($info, '访问地址错误，请核查')) {
        $Json = array(
            'code' => 201,
            'msg' => '访问地址错误，请核查',
        );
    } else if (empty($pass) && strpos($info, '输入密码')) {
        $Json = array(
            'code' => 202,
            'msg' => '请输入密码',
        );
    } else if (isset($pass) && strpos($info, '输入密码')) {
        $Json = array(
            'code' => 203,
            'msg' => '密码已输入',
        );
    } else {
        $Json = array(
            'code' => 200,
            'msg' => '已获取到文件!',
        );
    }
    return $Json;
}

//获取文件信息
function fileInfo($info)
{
    preg_match("/<div class=\"md\">(.*?)<span class=\"mtt\">/", $info, $name);
    preg_match('/时间:<\\/span>(.*?)<span class="mt2">/u', $info, $time);
    preg_match('/发布者:<\\/span>(.*?)<span class="mt2">/u', $info, $author);
    preg_match('/<div class="md">(.*?)<span class="mtt">\\((.*?)\\)<\\/span><\\/div>/', $info, $size);
    preg_match('/var tedomain = \'(.*?)\'/', $info, $tedomain);
    preg_match('/var domianload = \'(.*?)\'/', $info, $domainload);
    return array(
        'name' => $name[1],
        'time' => $time[1],
        'author' => $author[1],
        'size' => $size[2],
        'download' => $tedomain[1].$domainload[1],
    );
}

//获取下载链接
function getRedirect($url, $ref = '')
{
    $headers = array(
        'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8',
        'Accept-Encoding: gzip, deflate',
        'Accept-Language: zh-CN,zh;q=0.9',
        'Cache-Control: no-cache',
        'Connection: keep-alive',
        'Pragma: no-cache',
        'Upgrade-Insecure-Requests: 1',
    );
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    if ($ref) {
        curl_setopt($curl, CURLOPT_REFERER, $ref);
    }
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLINFO_HEADER_OUT, true);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_exec($curl);
    $url = curl_getinfo($curl);
    curl_close($curl);
    return $url['redirect_url'];
}

//参数名获取
function parameter_name($name, $info)
{
    $pos = strpos($info, $name);
    $info2 = substr($info, $pos + strlen($name));
    $pos = strpos($info2, ',');
    return substr($info2, 0, $pos);
}

//获取参数值
function parameter_value($name, $info)
{
    $pos = strpos($info, $name);
    $info2 = substr($info, $pos + strlen($name) + 4);
    $pos = strpos($info2, ';');
    return substr($info2, 0, $pos - 1);
}

//获取参数值
function get_var($name, $info)
{
    $pos = strpos($info, $name);
    $info2 = substr($info, $pos + strlen($name));
    $pos = strpos($info2, ';');
    return substr($info2, 0, $pos);
}

//发送密码校验
function send_post($url, $post_data)
{
    $postdata = http_build_query($post_data);
    $options = array('http' => array(
        'method' => 'POST',
        'header' =>  "Content-Type: application/x-www-form-urlencoded\r\n".'Referer: ' . $GLOBALS['right'] . '/\\r\\n' . 'Accept-Language:zh-CN,zh;q=0.9\\r\\n',
        'content' => $postdata,
        'timeout' => 15 * 60,
    ));
    $context = stream_context_create($options);
    return file_get_contents($url, false, $context);
}

//信息校验准备
function info_prepare($lanzou, $pwd)
{
    //获取sign值
    preg_match('/var posign = \'(.*?)\';/', $lanzou, $sign);

    //如是获取失败,则为多文件
    if (empty($sign)) {
        //第一个校验参数名:
        $t = '\'t\':';
        //第二个校验参数名:
        $k = '\'k\':';
        $parameter['t']['name'] = parameter_name($t, $lanzou);
        $parameter['k']['name'] = parameter_name($k, $lanzou);
        $parameter['t']['value'] = parameter_value($parameter['t']['name'], $lanzou);
        $parameter['k']['value'] = parameter_value($parameter['k']['name'], $lanzou);
        $parameter['pgs']['value'] = get_var('pgs =', $lanzou);
        //传输参数
        $data_start = 'data :';
        $data_pos = strripos($lanzou, $data_start);
        $data_info = substr($lanzou, $data_pos);
        $data_end = '},';
        $data_pos = strpos($data_info, $data_end);
        $data_info = substr($data_info, strlen($data_start), $data_pos - strlen($data_end) * 2 - 1);
        //填充参数
        $data_info = str_replace('\'', '"', $data_info);
        $data_info = str_replace($parameter['t']['name'], '"' . $parameter['t']['value'] . '"', $data_info);
        $data_info = str_replace($parameter['k']['name'], '"' . $parameter['k']['value'] . '"', $data_info);
        $data_info = str_replace('pgs', $parameter['pgs']['value'], $data_info);
        $data_info = str_replace(':pwd', ':"' . $pwd . '"', $data_info);
        $post_data = json_decode($data_info, true);
        $pwdurl = send_post($GLOBALS['right'] . '/filemoreajax.php', $post_data);
        $obj = json_decode($pwdurl, true);
        if ($obj['zt'] == 1) {
            foreach ($obj['text'] as $item) {
                $url = getInfo(0, $item['id'], 1);
                $info[] = fileInfo($url);
            }
        } else {
            $info = "密码错误!";
        }
    } else {
        //单文件
        $post_data = array('action' => 'downprocess', 'sign' => $sign[1], 'p' => $pwd);
        $pwdurl = send_post($GLOBALS['right'] . '/ajaxm.php', $post_data);
        $obj = json_decode($pwdurl, true);
        if ($obj['zt'] == 1) {
            $download = getRedirect($obj['dom'] . '/file/' . $obj['url']);
            $file_info = fileInfo($lanzou);
            $info[] = array(
                'name' => $file_info['name'],
                'author' => $file_info['author'],
                'time' => $file_info['time'],
                'size' => $file_info['size'],
                'download' => $download
            );
        } else {
            $info = "密码错误!";
        }
    }
    return $info;
}
header('Access-Control-Allow-Origin:*');
header('Content-type: application/json;charset=utf-8');
error_reporting(0);
//获取所需的参数
$url = @$_REQUEST['url'];
$pass = @$_REQUEST['pass'];
$type = @$_REQUEST['type'];
//接口
const urls = ['https://www.lanzoui.com','https://wwwx.lanzoux.com'];

//开始处理
if (!empty($url)){
    //获取文件ID
    $id = GetFileID($url, 'com/', '/');
    //获取页面信息
    $pageInfo = getInfo(0, $id, 1);
    $Json = check_status($pageInfo, $pass);
     if ($Json['code']===200){
         //单文件解析
         $file_info = fileInfo($pageInfo);
         $Json = array(
             'code' => 200,
             'data' => array(
                 'name' => $file_info['name'],
                 'author' => $file_info['author'],
                 'time' => $file_info['time'],
                 'size' => $file_info['size'],
                 'url' => $file_info['download']
             )
         );
     }else if ($Json['code']===203){
         //已提交密码,准备发送校验
         $info = info_prepare($pageInfo, $pass);
         if ($info === '密码错误!') {
             $Json = array(
                 'code' => 202,
                 'msg' => '密码不正确',
             );
         } else {
             //密码解析完成
             foreach ($info as $file_info){
                 $data[] = array(
                     'name' => $file_info['name'],
                     'author' => $file_info['author'],
                     'time' => $file_info['time'],
                     'size' => $file_info['size'],
                     'url' => $file_info['download']
                 );
             }
             $Json = array(
                 'code' => 200,
                 'data' => $data,
             );
         }
     }
}else{
    //链接为空
    $Json = array(
        'code' => 201,
        'msg' => '请输入需要解析的蓝奏链接'
    );
}

//输出JSON信息
if (!empty($Json)) {
    echo json_encode($Json, JSON_NUMERIC_CHECK | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}