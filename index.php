<?php
require_once "./function.php";
header('Access-Control-Allow-Origin:*');
header('Content-type: application/json;charset=utf-8');
error_reporting(0);

const API = 'https://www.lanzoui.com';

function main()
{
    //获取所需参数
    $id = @$_REQUEST['id'];
    $url = @$_REQUEST['url'];
    $pass = @$_REQUEST['pass'];

    if (empty($url) && empty($id)) {
        return [
            'code' => 201,
            'msg' => '请输入需要解析的蓝奏链接或文件ID'
        ];
    }

    $fileId = "";
    if ($id) {
        $fileId = $id;
    } else if ($url) {
        //获取文件ID
        preg_match('/[^\/]+$/', $url, $match);
        if (!$match) {
            return [
                'code' => 201,
                'msg' => '链接有误'
            ];
        }
        $fileId = $match[0];
    }

    //获取页面信息
    $pageInfo = getInfo($fileId);
    $status = check_status($pageInfo, $pass);

    if ($status['code'] === 200) {
        //单文件
        return [
            'code' => 200,
            'data' => [
              fileInfo($pageInfo)
            ]
        ];
    } else if ($status['code'] === 999) {
        //有密码或文件夹
        return info_prepare($pageInfo, $pass, $fileId);
    } else {
        return $status;
    }
}

//输出JSON信息
echo json_encode(main());

