# LanZou_API
蓝奏云获取直链/蓝奏云直链解析
支持文件夹解析

### 使用方法
- url:蓝奏云外链链接
- pwd:外链密码

### 请求示例
<p>无密码 <a href="https://api.huankong.top/lanzou/?url=https://huankong233.lanzouj.com/ikMixwq817e">https://api.huankong.top/lanzou/?url=https://huankong233.lanzouj.com/ikMixwq817e</a></p>
<p>有密码 <a href="https://api.huankong.top/lanzou/?url=https://huankong233.lanzouj.com/io7zInot1vi&pwd=994i">https://api.huankong.top/lanzou/?url=https://huankong233.lanzouj.com/io7zInot1vi&pwd=994i</a></p>
<p>文件夹 <a href="https://api.huankong.top/lanzou/?url=https://huankong233.lanzouj.com/b0e9c6qwf&pass=gto6">https://api.huankong.top/lanzou/?url=https://huankong233.lanzouj.com/b0e9c6qwf&pass=gto6</a></p>


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
