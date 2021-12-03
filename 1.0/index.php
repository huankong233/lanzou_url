<?php
require_once "./func.php";
header('Access-Control-Allow-Origin:*');
header('Content-type: application/json;charset=utf-8');
error_reporting(0);
$url = $_REQUEST['url'];
$pwd = $_REQUEST['pwd'];
$type = $_REQUEST['type'];
$GLOBALS['right'] = '';
const lanzous = ['https://wwwx.lanzoux.com', 'https://www.lanzoui.com', 'https://www.lanzouw.com'];

if (!empty($url)) {
    $b = 'com/';
    $c = '/';
    $id = GetBetween($url, $b, $c);
    $lanzou = getInfo(0, $id, 1);
    $Json = check_status($lanzou, $pwd);
    if (empty($Json)) {
        $file_info = file_info($lanzou);
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
    } else {
        if (!empty($pwd)) {
            if ($Json['code'] !== 203) {
                //单文件
                $file_info = file_info($lanzou);
                $download = info_process($lanzou, $pwd);
                if ($download == '密码错误!') {
                    $Json = array(
                        'code' => 202,
                        'msg' => '密码不正确',
                    );
                } else {
                    $Json = array(
                        'code' => 200,
                        'data' => array(
                            array(
                                'name' => $file_info['name'],
                                'author' => $file_info['author'],
                                'time' => $file_info['time'],
                                'size' => $file_info['size'],
                                'url' => $download
                            )
                        )
                    );
                }
            } else {
                //多文件
                //下载链接+文件ID
                $info = info_process($lanzou, $pwd);
                if ($info == '密码错误!') {
                    $Json = array(
                        'code' => 202,
                        'msg' => '密码不正确',
                    );
                } else {
                    foreach ($info as $item) {
                        $arr[] = array(
                            'name' => $item['name'],
                            'author' => $item['author'],
                            'time' => $item['time'],
                            'size' => $item['size'],
                            'url' => $item['download']
                        );
                    }
                    $Json = array(
                        'code' => 200,
                        'data' => $arr,
                    );
                }
            }
        } else {
            $Json = check_status($lanzou, $pwd);
            if (empty($Json)) {
                $Json = array(
                    'code' => 202,
                    'msg' => '请输入密码',
                );
            } else {
                if ($Json['code'] == 203) {
                    $Json = array(
                        'code' => 202,
                        'msg' => '请输入密码',
                    );
                }
            }
        }
    }
} else {
    $Json = array(
        'code' => 201,
        'msg' => '请输入需要解析的蓝奏链接'
    );
}

//参数为down时下载
if ($type == 'down') {
    if (count($Json['data']) == 1) {
        header("Location:{$download}");
    } else {
        $Json = array(
            'code' => 200,
            'warning' => '多文件时，不支持直接下载!',
            'data' => $arr,
        );
    }
} else if ($type == 'url') {
    if (empty($arr)) {
        $Json = array('url' => $download);
    } else {
        foreach ($arr as $item) {
            $arr2[] = array(
                'name' => $item['name'],
                'url' => $item['url'],
            );
        }
        $Json = $arr2;
    }
}

//输出JSON格式文件
if (!empty($Json)) {
    echo json_encode($Json, JSON_NUMERIC_CHECK | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}

