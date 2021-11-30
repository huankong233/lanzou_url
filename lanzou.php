<?php
/**
 * 蓝奏直链解析
 * type接受参数: download
 * url接受参数: 需要解析的链接
 * pwd接受参数: 需要解析的链接的密码
 */

header('Access-Control-Allow-Origin:*');
header('Content-type: application/json');
error_reporting(0);
$url = $_REQUEST['url'];
$pwd = $_REQUEST['pwd'];
$type = $_REQUEST['type'];
if (!empty($url)) {
    $b = 'com/';
    $c = '/';
    $id = GetBetween($url, $b, $c);
    $d = 'https://www.lanzoui.com/tp/' . $id;
    $lanzou = curl($d);
    if (strpos($lanzou, '文件取消分享了') || empty($lanzou)) {
        $Json = array(
            'code' => 201,
            'msg' => '文件取消分享了',
        );
    }else if (strpos($lanzou, '文件不存在')){
        $Json = array(
            'code' => 201,
            'msg' => '文件不存在，或已删除',
        );
    } else {
        if (strpos($lanzou, '输入密码') && empty($pwd)) {
            $Json = array('code' => 202, 'msg' => '请输入密码');
        } else {
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
            if (!empty($pwd)) {
                preg_match('/sign\':\'(.*?)\'/', $lanzou, $sign);
                $post_data = array('action' => 'downprocess', 'sign' => $sign[1], 'p' => $pwd);
                $pwdurl = send_post('https://wwa.lanzous.com/ajaxm.php', $post_data);
                $obj = json_decode($pwdurl, true);
                $download = getRedirect($obj['dom'] . '/file/' . $obj['url']);
            }
            $Json = array(
                'code' => 200,
                'data' => array(
                    'name' => $name[1],
                    'author' => $author[1],
                    'time' => $time[1],
                    'size' => $size[2],
                    'url' => $download
                )
            );
            if (strpos($pwdurl, '"zt":0') !== false) {
                $Json = array(
                    'code' => 202,
                    'msg' => '密码不正确',
                );
            }
        }
    }
} else {
    $Json = array(
        'code' => 201,
        'msg' => '请输入需要解析的蓝奏链接'
    );
}

if ($type == 'down') {
    header("Location:{$download}");
}
if (!empty($Json)) {
    echo json_encode($Json, JSON_NUMERIC_CHECK | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}
function send_post($url, $post_data)
{
    $postdata = http_build_query($post_data);
    $options = array('http' => array(
        'method' => 'POST',
        'header' => 'Referer: https://www.lanzous.com/\\r\\n' . 'Accept-Language:zh-CN,zh;q=0.9\\r\\n',
        'content' => $postdata,
        'timeout' => 15 * 60,
    ));
    $context = stream_context_create($options);
    $result = file_get_contents($url, false, $context);
    return $result;
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
