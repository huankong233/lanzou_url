# LanZou_API
(暂时无法正常使用！)
蓝奏云获取直链/蓝奏云直链解析

### 使用方法
- url:蓝奏云外链链接
- pwd:外链密码
<!--more-->

### 支持链接
- \*.lanzous.com
- \*.lanzoui.com
- \*.lanzoux.com

### 请求示例
<p>无密码 <a href="https://api.huankong.top/lanzou/?url=https://wwx.lanzoux.com/ikMixwq817e">https://api.huankong.top/lanzou/?url=https://wwx.lanzoux.com/ikMixwq817e</a></p>
<p>有密码 <a href="https://api.huankong.top/lanzou/?url=https://wwx.lanzoux.com/ikMixwq817e&pwd=d17u">https://api.huankong.top/lanzou/?url=https://wwx.lanzoux.com/ikMixwq817e&pwd=d17u</a></p>


### 返回数据
~~~ json
{
    "code": 200,
    "data": {
        "name": "msyh.ttf ",
        "author": "15** ",
        "time": "12 天前 ",
        "size": " 14.4 M ",
        "url": "https://dev46.baidupan.com/120322bb/2021/11/21/1adb9e3cd76cd776a428280349147aef.ttf?st=fNgkfh4hZSYhhtepVDYS6w&e=1638542112&b=CDcOfQJ7UD1WLQclACQEZg_c_c&fi=56698784&pid=165-154-75-88&up=2&mp=0"
    }
}
~~~

|code| 返回值|
| ------ | ------ |
| 200 | 解析成功 |
| 201 | 链接失效 |
| 202 | 密码错误/请输入密码 |
