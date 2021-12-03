<?php
//参数名获取
function parameter_name($name, $info)
{
    $pos = strpos($info, $name);
    $info2 = substr($info, $pos + strlen($name));
    $pos = strpos($info2, ',');
    return substr($info2, 0, $pos);
}

//参数值获取
function parameter_value($name, $info)
{
    $pos = strpos($info, $name);
    $info2 = substr($info, $pos + strlen($name) + 4);
    $pos = strpos($info2, ';');
    return substr($info2, 0, $pos - 1);
}

function get_var($name, $info)
{
    $pos = strpos($info, $name);
    $info2 = substr($info, $pos + strlen($name));
    $pos = strpos($info2, ';');
    return substr($info2, 0, $pos);
}

//获取链接
function getURL($key, $id, $mode)
{
    //首创支持多文件处理
    if ($mode == 1) {
        $d = lanzous[$key] . '/tp/' . $id;
        return curl($d);
    } else if ($mode == 2) {
        $d = lanzous[$key] . '/' . $id;
        return curl($d);
    }
}

//获取页面信息
function getInfo($key, $id, $mode)
{
    if ($key > count(lanzous)) {
        if ($mode == 2) {
            $Json = array(
                'code' => 201,
                'msg' => '文件信息获取失败!',
            );
            echo json_encode($Json, JSON_NUMERIC_CHECK | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            die();
        } else {
            $mode += 1;
            return getInfo(0, $id, $mode);
        }
    } else {
        $lanzous = getURL($key, $id, $mode);
        if (empty($lanzous) || $lanzous == false) {
            $key += 1;
            return getInfo($key, $id, $mode);
        } else {
            $GLOBALS['right'] = lanzous[$key];
            return $lanzous;
        }
    }
}

//密码校验
function send_post($url, $post_data)
{
    $postdata = http_build_query($post_data);
    $options = array('http' => array(
        'method' => 'POST',
        'header' => 'Referer: ' . $GLOBALS['right'] . '/\\r\\n' . 'Accept-Language:zh-CN,zh;q=0.9\\r\\n',
        'content' => $postdata,
        'timeout' => 15 * 60,
    ));
    $context = stream_context_create($options);
    $result = file_get_contents($url, false, $context);
    return $result;
}

//请求状态
function check_status($info, $pwd)
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
    } else if (strpos($info, '显示更多文件')) {
        $Json = array(
            'code' => 203,
            'msg' => '链接存在多个文件',
        );
    } else if (strpos($info, '输入密码') && empty($pwd)) {
        $Json = array(
            'code' => 202,
            'msg' => '请输入密码',
        );
    } else if (strpos($info, '输入密码') && isset($pwd)) {
        $Json = array(
            'code' => 202,
            'msg' => '密码已输入，进入检测环节',
        );
    }
    return $Json;
}

//信息提交准备
function info_process($lanzou, $pwd)
{
    preg_match('/sign\':\'(.*?)\'/', $lanzou, $sign);
    //如是文件夹
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
        if ($obj['info']=='sucess'){
            foreach ($obj['text'] as $item) {
                $url = getURL(0, $item['id'], 1);
                $info[] = file_info($url);
            }
        }else{
            $info = "密码错误!";
        }
    } else {
        $post_data = array('action' => 'downprocess', 'sign' => $sign[1], 'p' => $pwd);
        $pwdurl = send_post($GLOBALS['right'] . '/ajaxm.php', $post_data);
        $obj = json_decode($pwdurl, true);
        if ($obj['zt']==1){
            $info = getRedirect($obj['dom'] . '/file/' . $obj['url']);
        }else{
            $info = "密码错误!";
        }
    }
    return $info;
}

//输出文件信息
function file_info($lanzou): array
{
    preg_match("/<div class=\"md\">(.*?)<span class=\"mtt\">/", $lanzou, $name);
    preg_match('/时间:<\\/span>(.*?)<span class=\\"mt2\\">/', $lanzou, $time);
    preg_match('/发布者:<\\/span>(.*?)<span class=\\"mt2\\">/', $lanzou, $author);
    preg_match('/var domianload = \'(.*?)\';/', $lanzou, $down1);
    preg_match('/domianload \+ \'(.*?)\'/', $lanzou, $down2);
    preg_match('/var downloads = \'(.*?)\'/', $lanzou, $down3);
    preg_match('/<div class=\\"md\\">(.*?)<span class=\\"mtt\\">\\((.*?)\\)<\\/span><\\/div>/', $lanzou, $size);
    if (!empty($down2)) {
        $download = getRedirect($down1[1] . $down2[1]);
    } else {
        $download = getRedirect($down1[1] . $down3[1]);
    }
    return array(
        'name' => $name[1],
        'time' => $time[1],
        'author' => $author[1],
        'down1' => $down1[1],
        'down2' => $down2[1],
        'down3' => $down3[1],
        'size' => $size[2],
        'download' => $download,
    );
}

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

function GetBetween($content, $start, $end)
{
    $r = explode($start, $content);
    if (isset($r[1])) {
        $r = explode($end, $r[1]);
        return $r[0];
    }
    return '';
}

//获取直链
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
    $data = curl_exec($curl);
    $url = curl_getinfo($curl);
    curl_close($curl);
    return $url['redirect_url'];
}