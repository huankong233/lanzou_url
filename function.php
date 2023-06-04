<?php
/**
 * 调用Curl
 * @param string $url (地址)
 * @return bool|string
 */
function curl($url)
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
    return curl_exec($ch);
}

/**
 * 获取页面信息
 * @param string $id (传入的ID)
 * @return array|bool|string
 */
function getInfo($id)
{
    //获取页面数据(单文件)
    $url = API . '/tp/' . $id;
    $info = curl($url);
    if (empty($info)) {
        //获取页面数据(多文件)
        $url = API . '/' . $id;
        $info = curl($url);
        if (empty($info)) {
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
            'code' => 999,
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
function fileInfo($info, $mode = 1)
{

    preg_match("/<div class=\"md\">(.*)<span class=\"mtt\">\(\ (.*)\ \)<\/span>/", $info, $fileInfo);
    $fileName = trim($fileInfo[1]);
    $fileSize = trim($fileInfo[2]);

    preg_match('/时间:<\/span>(.*)<span class=\"mt2\">发布者:<\/span>(.*)<span class="mt2">/', $info, $fileInfo);
    $fileTime = trim($fileInfo[1]);
    $fileAuthor = trim($fileInfo[2]);

    $arr = [
        'fileName' => $fileName,
        'fileSize' => $fileSize,
        'fileTime' => $fileTime,
        'fileAuthor' => $fileAuthor,
        'fileUrl' => ""
    ];

    if ($mode === 1) {
        preg_match('/submit.href = (.*)/', $info, $downloadParams);
        $downloadParams = trim($downloadParams[1]);
        foreach (explode('+', $downloadParams) as $downloadParam) {
            $downloadParam = trim($downloadParam);
            preg_match('/' . $downloadParam . ' = \'(.*?)\'/', $info, $param);
            $arr['fileUrl'] .= $param[1];
        }
    }

    return $arr;
}

//信息校验准备
function info_prepare($content, $pwd, $fileId)
{
    //获取sign变量对应名称
    preg_match('/\'sign\':(.*?),/', $content, $var);
    //如是获取成功就是单文件
    if ($var) {
        //获取sign值
        preg_match('/var ' . $var[1] . ' = \'(.*?)\';/', $content, $sign);
        //单文件
        return get_data(1, ['action' => 'downprocess', 'sign' => $sign[1], 'p' => $pwd], $content, $fileId);
    } else {
        preg_match("/'lx':([\d]*),/", $content, $lx);
        preg_match("/'fid':([\d]*),/", $content, $fid);
        preg_match("/'uid':'([\d]*)',/", $content, $uid);
        preg_match("/pgs =([\d]*)/", $content, $pgs);
        preg_match("/'rep':'([\d]*)',/", $content, $rep);
        preg_match("/var [0-9a-z]{6} = '(\d{10})';/", $content, $t);
        preg_match("/var [_0-9a-z]{6} = '([0-9a-z]{15,})';/", $content, $k);
        preg_match("/'up':([\d]*),/", $content, $up);
        preg_match("/'ls':([\d]*),/", $content, $ls);
        return get_data(2, [
            'lx' => (int)$lx[1],
            'fid' => (int)$fid[1],
            'uid' => $uid[1],
            'pg' => (int)$pgs[1],
            'rep' => $rep[1],
            't' => $t[1],
            'k' => $k[1],
            'up' => (int)$up[1],
            'ls' => (int)$ls[1],
            'pwd' => $pwd
        ], $content, $fileId);
    }
}


//发送密码校验
function send_post($url, $post_data, $fileId)
{
    preg_match("/\/\/(.*)/",API,$HOST);
    $postdata = http_build_query($post_data);
    $options = [
        'http' => [
            'method' => 'POST',
            'header' => "Content-Type: application/x-www-form-urlencoded\r\n" .
                "Host: " . $HOST[1] . "\r\n" .
                "Origin: " . API . "\r\n" .
                "Referer: " . API . "/" . $fileId . "\r\n" .
                "Accept: application/json, text/javascript, */*\r\n" .
                "Accept-Encoding: gzip, deflate, br\r\n" .
                "Accept-Language: zh-CN,zh;q=0.9\r\n" .
                "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/107.0.0.0 Safari/537.36\r\n",
            'content' => $postdata,
            'timeout' => 15 * 60,
        ]
    ];
    $context = stream_context_create($options);
    return file_get_contents($url, false, $context);
}

/**
 * 获取数据
 * @param $type (1为单文件，2为多文件)
 * @param $params
 * @param $content
 * @param $fileId
 * @return array
 */
function get_data($type, $params, $content, $fileId)
{
    if ($type === 1) {
        $pwdurl = send_post(API . '/ajaxm.php', $params, $fileId);
        $obj = json_decode($pwdurl, true);
        if ($obj['zt'] === 1) {
            $file_info = fileInfo($content, 2);
            return [
                'code' => 200,
                'data' => [
                    [
                        'fileName' => $file_info['fileName'],
                        'fileSize' => $file_info['fileSize'],
                        'fileTime' => $file_info['fileTime'],
                        'fileAuthor' => $file_info['fileAuthor'],
                        'download' => $obj['dom'] . '/file/' . $obj['url']
                    ]
                ]
            ];
        } else {
            return [
                'code' => 201,
                'msg' => '密码错误!'
            ];
        }
    } else if ($type === 2) {
        $page = @$_REQUEST['page'];
        if (isset($page) && (int)$page !== 1) {
            $pgs = (int)$page;
            for ($i = 1; $i < $pgs; $i++) {
                $params['pg'] = $i;
                send_post(API . '/filemoreajax.php', $params, $fileId);
                sleep(1);
            }
            $params['pg'] = $pgs;
        }
        return parse(send_post(API . '/filemoreajax.php', $params, $fileId));
    }
}

function parse($content)
{
    $obj = json_decode($content, true);
    if ($obj['zt'] === 1) {
        $data = [];
        foreach ($obj['text'] as $item) {
            $data[] = [
                'fileName' => $item['name_all'],
                'fileSize' => $item['size'],
                'fileTime' => $item['time'],
                'fileId' => $item['id'],
            ];
        }
        return [
            'code' => 200,
            'data' => $data
        ];
    } else {
        return [
            'code' => 203,
            'zt' => $obj['zt'],
            'msg' => $obj['info']
        ];
    }
}
