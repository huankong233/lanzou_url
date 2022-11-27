<?php
require_once "./function.php";
header('Access-Control-Allow-Origin:*');
header('Content-type: application/json;charset=utf-8');
error_reporting(0);

const api = 'https://www.lanzoui.com';

function main(){
    if(!isset($_REQUEST['url']) && !isset($_REQUEST['pass']) && !isset($_REQUEST['type'])){
        return [
            'code' => 201,
            'msg' => '参数不足!'
        ];
    }
    //获取所需参数
    $url = @$_REQUEST['url'];
    $pass = @$_REQUEST['pass'];
    $type = @$_REQUEST['type'];

    if(empty($url)){
        return  [
            'code' => 201,
            'msg' => '请输入需要解析的蓝奏链接'
        ];
    }

    //获取文件ID
    preg_match('/[^\/]+$/',$url,$match);
    if(!$match){
        return  [
            'code' => 201,
            'msg' => '链接有误'
        ];
    }
    $id = $match[0];

    //获取页面信息
    $pageInfo = getInfo($id);
    $status = check_status($pageInfo, $pass);
    if ($status['code']===200){
        //单文件
        $file_info = fileInfo($pageInfo,true);
        return [
            'code' => 200,
            'data' => [
                'name' => $file_info['name'],
                'author' => $file_info['author'],
                'time' => $file_info['time'],
                'size' => $file_info['size'],
                'url' => $file_info['download']
            ]
        ];
    }else if ($status['code']===203){
        //有密码或文件夹
        return info_prepare($pageInfo, $pass);
    }
    return $status;
}

//输出JSON信息
echo json_encode(main());

