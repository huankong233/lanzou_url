<?php
/**
 * 调用Curl
 * @param $url (地址)
 * @param int $mode (mode为1时为默认，mode为2时为获取重定向)
 * @return bool|mixed|string
 */
function curl($url,$mode=1,$post_data=[])
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8',
        'Accept-Encoding: gzip, deflate',
        'Accept-Language: zh-CN,zh;q=0.9',
        'Cache-Control: no-cache',
        'Connection: keep-alive',
        'Pragma: no-cache',
        'Upgrade-Insecure-Requests: 1'
    ]);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (iPhone; CPU iPhone OS 6_0 like Mac OS X) AppleWebKit/536.26 (KHTML, like Gecko) Version/6.0 Mobile/10A5376e Safari/8536.25');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_ENCODING, 'gzip,deflate');
    if($mode===1){
        return curl_exec($ch);
    }else if($mode===2){
        curl_setopt($ch, CURLOPT_REFERER, '');
        return curl_getinfo($ch)['redirect_url'];
    }
}

/**
 * 获取页面信息
 * @param $id (传入的ID)
 * @return array|bool|string
 */
function getInfo($id)
{
    //获取页面数据(单文件)
    $url = api . '/tp/' . $id;
    $info = curl($url,1);
    if (empty($info) || $info === false) {
        //获取页面数据(多文件)
        $url = api . '/' . $id;
        $info = curl($url,1);
        if (empty($info) || $info === false) {
            return [
                'code' => 201,
                'msg' => '文件信息获取失败!'
            ];
        }
    }
    return $info;
}

//请求状态
function check_status($info, $pass)
{
    if (strpos($info, '文件取消分享了')) {
        return [
            'code' => 201,
            'msg' => '文件取消分享了',
        ];
    } else if (strpos($info, '文件不存在')) {
        return [
            'code' => 201,
            'msg' => '文件不存在，或已删除',
        ];
    } else if (strpos($info, '访问地址错误，请核查')) {
        return [
            'code' => 201,
            'msg' => '访问地址错误，请核查',
        ];
    } else if (empty($pass) && strpos($info, '输入密码')) {
        return [
            'code' => 202,
            'msg' => '请输入密码',
        ];
    } else if (isset($pass) && strpos($info, '输入密码')) {
        return [
            'code' => 203,
            'msg' => '密码已输入',
        ];
    } else {
        return [
            'code' => 200,
            'msg' => '已获取到文件!',
        ];
    }
}

//获取文件信息
function fileInfo($info,$mode=false)
{
    preg_match("/<div class=\"md\">(.*?)<span class=\"mtt\">/", $info, $name);
    preg_match('/时间:<\\/span>(.*?)<span class="mt2">/u', $info, $time);
    preg_match('/发布者:<\\/span>(.*?)<span class="mt2">/u', $info, $author);
    preg_match('/<div class="md">(.*?)<span class="mtt">\\((.*?)\\)<\\/span><\\/div>/', $info, $size);
    preg_match('/var tedomain = \'(.*?)\'/', $info, $tedomain);
    preg_match('/var domianload = \'(.*?)\'/', $info, $domainload);
    $arr = [
        'name' => $name[1],
        'time' => $time[1],
        'author' => $author[1],
        'size' => $size[2]
    ];
    if($mode){
        $arr['download'] = $tedomain[1].$domainload[1];
    }
    return $arr;
}

//发送密码校验
function send_post($url, $post_data)
{
    $postdata = http_build_query($post_data);
    $options = [
        'http' => [
            'method' => 'POST',
            'header' =>  "Content-Type: application/x-www-form-urlencoded\r\n"."Origin: ".api."\r\nReferer:".@$_REQUEST['url']."\r\n"."Accept-Language:zh-CN,zh;q=0.9\r\nMozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/107.0.0.0 Safari/537.36\r\n",
            'content' => $postdata,
            'timeout' => 15 * 60,
        ]
    ];
    $context = stream_context_create($options);
    return file_get_contents($url, false, $context);
}

//信息校验准备
function info_prepare($content, $pwd)
{
    //获取posign值
    preg_match('/var posign = \'(.*?)\';/', $content, $sign);
    //如是获取成功就是单文件
    if($sign){
        //单文件
        return get_data(1,['action' => 'downprocess', 'sign' => $sign[1], 'p' => $pwd],$content);
    }else{
        //获取两个变量
        preg_match_all("/var pgs;(([.\n]*).*)(([.\n]*).*)/",$content,$match);
        $first = $match[1][0];
        $second = $match[3][0];
        preg_match("/var (([.\n]*).*) = (([.\n]*).*);/",$first,$first);
        preg_match("/var (([.\n]*).*) = (([.\n]*).*);/",$second,$second);
        $firstParams[$first[1]] = $first[3];
        $firstParams[$second[1]] = $second[3];

        //获取其余参数
        preg_match_all("/data : { ((([.\n]*).*){10})}/",$content,$match);
        $data = $match[1][0];
        $data = str_replace("	","",$data);
        $data = str_replace("\n","",$data);
        $data = explode(",",$data);

        //填充参数
        $params = [];
        $pgs = 1;
        foreach ($data as $datum){
            $arr = explode(":",$datum);
            switch ($arr[0]){
                case "'rep'":
                case "'uid'":
                    preg_match("/'(([.\n]*).*)'/",$arr[1],$match);
                    $arr[1] = $match[1];
                    break;
                case "'fid'":
                case "'lx'":
                case "'up'":
                case "'ls'":
                    $arr[1] = (int)$arr[1];
                    break;
                case "'pg'":
                    $arr[1] = $pgs;
                    break;
                case "'t'":
                case "'k'":
                    foreach ($firstParams as $key => $firstParam){
                        if($key === $arr[1]){
                            preg_match("/'(([.\n]*).*)'/",$firstParam,$match);
                            $arr[1] = $match[1];
                        }
                    }
                    break;
                case "'pwd'":
                    $arr[1] = $pwd;
                    break;
            }
            preg_match("/'(([.\n]*).*)'/",$arr[0],$name);
            $params[$name[1]] = $arr[1];
        }
        return get_data(2,$params,$content);
    }
}

/**
 * 获取数据
 * @param $type (1为单文件，2为多文件)
 * @param $params
 * @param $content
 * @param $data
 * @return array
 */
function get_data($type,$params,$content){
    if($type === 1){
        $pwdurl = send_post(api.'/ajaxm.php',$params);
        $obj = json_decode($pwdurl, true);
        if ($obj['zt'] === 1) {
            $file_info = fileInfo($content);
            return [
                'code'=>200,
                'data'=>[
                    'name' => $file_info['name'],
                    'author' => $file_info['author'],
                    'time' => $file_info['time'],
                    'size' => $file_info['size'],
                    'download' => $obj['dom'] . '/file/' . $obj['url']
                ]
            ];
        } else {
            return [
                'code'=>201,
                'msg'=>'密码错误!'
            ];
        }
    }else if($type === 2) {
        $page = @$_REQUEST['page'];
        if ($page) {
            $pgs = (int)$page;
            $params['pg'] = $pgs;
        }
        $pwdurl = send_post(api . '/filemoreajax.php', $params);
        return parse($pwdurl);
    }
}

function parse($content){
    $obj = json_decode($content, true);
    if ($obj['zt'] === 1) {
        $data = [];
        foreach ($obj['text'] as $item) {
            $url = getInfo($item['id']);
            $data[] = fileInfo($url,true);
            sleep(0.6);
        }
        return [
            'code' => 200,
            'data' => $data
        ];
    }else{
        return [
            'code' => 201,
            'zt' => $obj['zt'],
            'msg' => $obj['info']
        ];
    }
}
