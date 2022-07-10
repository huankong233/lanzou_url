<?php
require_once "./func.php";
header('Access-Control-Allow-Origin:*');
header('Content-type: application/json;charset=utf-8');
error_reporting(0);
//获取所需的参数
$url = $_REQUEST['url'];
$pass = $_REQUEST['pass'];
$type = $_REQUEST['type'];
//接口
const urls = ['https://wwwx.lanzouj.com','https://wwwx.lanzoux.com', 'https://www.lanzoui.com', 'https://www.lanzouw.com'];

//开始处理
if (!empty($url)){
    //获取文件ID
    $id = GetUrlID($url, 'com/', '/');
    //获取页面信息
    $pageInfo = getInfo(0, $id, 1);
    $Json = check_status($pageInfo, $pass);
    if ($Json['code']==200){
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
    }else if ($Json['code']==203){
        //已提交密码,准备发送校验
        $info = info_prepare($pageInfo, $pass);
        if ($info == '密码错误!') {
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
