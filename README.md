# LanZou_API
蓝奏云获取直链/蓝奏云直链解析
支持文件夹解析
注意!!!文件夹内文件数超过50会无法显示，因为需要翻页，翻页我折腾一下午没弄出来，就砍掉了()

## 部署方法

### 直接部署到php服务器上
下载最新的v2或v3文件夹，丢到服务器上即可

### 部署到Vercel
Tip:请求时需要在路径上加上/api，例子:https://test123-huankong233.vercel.app/api
<a href="https://vercel.com/import/project?template=https://github.com/huankong233/lanzou_url/tree/main/Vercel_v2" target="_blank" rel="noopener noreferrer" class="link-instanted"><img src="https://vercel.com/button" alt=""><span><svg class="external-link-icon" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false" x="0px" y="0px" viewBox="0 0 100 100" width="15" height="15"><path fill="currentColor" d="M18.8,85.1h56l0,0c2.2,0,4-1.8,4-4v-32h-8v28h-48v-48h28v-8h-32l0,0c-2.2,0-4,1.8-4,4v56C14.8,83.3,16.6,85.1,18.8,85.1z"></path><polygon fill="currentColor" points="45.7,48.7 51.3,54.3 77.2,28.5 77.2,37.2 85.2,37.2 85.2,14.9 62.8,14.9 62.8,22.9 71.5,22.9"></polygon></svg></span></a>


## 使用方法
- 使用部署时的url地址
- url:蓝奏云外链链接
- pwd:外链密码

### 请求示例
<p>无密码 <a href="https://api.huankong.top/lanzou/?url=https://huankong233.lanzouj.com/ikMixwq817e">https://api.huankong.top/lanzou/?url=https://huankong233.lanzouj.com/ikMixwq817e</a></p>
<p>有密码 <a href="https://api.huankong.top/lanzou/?url=https://huankong233.lanzouj.com/io7zInot1vi&pass=994i">https://api.huankong.top/lanzou/?url=https://huankong233.lanzouj.com/io7zInot1vi&pass=994i</a></p>
<p>文件夹 <a href="https://api.huankong.top/lanzou/?url=https://huankong233.lanzoue.com/b0e9c4bsj&pass=1omo">https://api.huankong.top/lanzou/?url=https://huankong233.lanzoue.com/b0e9c4bsj&pass=1omo</a></p>

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
| 201 | 链接失效/文件取消分享了/文件不存在或已删除等错误 |
| 202 | 请输入密码 |

