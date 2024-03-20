# LanZou_API

蓝奏云获取直链/蓝奏云直链解析

支持文件夹解析,翻页

## 部署方法

### 直接部署到php服务器上

克隆项目，丢到服务器上即可

### 部署到Vercel

<a href="https://vercel.com/import/project?template=https://github.com/huankong233/lanzou_url/tree/main/Vercel" target="_blank" rel="noopener noreferrer" class="link-instanted"><img src="https://vercel.com/button" alt=""><span><svg class="external-link-icon" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false" x="0px" y="0px" viewBox="0 0 100 100" width="15" height="15"><path fill="currentColor" d="M18.8,85.1h56l0,0c2.2,0,4-1.8,4-4v-32h-8v28h-48v-48h28v-8h-32l0,0c-2.2,0-4,1.8-4,4v56C14.8,83.3,16.6,85.1,18.8,85.1z"></path><polygon fill="currentColor" points="45.7,48.7 51.3,54.3 77.2,28.5 77.2,37.2 85.2,37.2 85.2,14.9 62.8,14.9 62.8,22.9 71.5,22.9"></polygon></svg></span></a>

Q： 频繁出现5xx报错？

A：ReDeploy即可 不要勾选缓存

## 使用方法

- 使用部署时的url地址
- url:蓝奏云外链链接
- fileId:蓝奏云文件ID
- pass:外链密码
- page:页数（如果一个文件夹内文件超过50需要翻页）

### 请求示例

[无密码: https://api.huankong.top/lanzou/?url=https://huankong233.lanzouj.com/ikMixwq817e](https://api.huankong.top/lanzou/?url=https://huankong233.lanzouj.com/ikMixwq817e)

[有密码: https://api.huankong.top/lanzou/?url=https://huankong233.lanzouj.com/io7zInot1vi&pass=994i](https://api.huankong.top/lanzou/?url=https://huankong233.lanzouj.com/io7zInot1vi&pass=994i)

[文件夹: https://api.huankong.top/lanzou/?url=https://huankong233.lanzoue.com/b0eay044h&pass=35su](https://api.huankong.top/lanzou/?url=https://huankong233.lanzoue.com/b0eay044h&pass=35su)

[文件夹(第二页): https://api.huankong.top/lanzou/?url=https://huankong233.lanzoue.com/b0eay044h&pass=35su&page=2](https://api.huankong.top/lanzou/?url=https://huankong233.lanzoue.com/b0eay044h&pass=35su&page=2)

[文件夹中文件的ID: https://api.huankong.top/lanzou/?fileId=145145](https://api.huankong.top/lanzou/?fileId=145145)

### 返回数据

~~~ json
{
  "code":200,
  "data":[
    {
      "fileName":"msyh.ttf",
      "fileSize":"14.4 M",
      "fileTime":"2021-11-21",
      "fileAuthor":"15**",
      "fileUrl":"https:\/\/developer.lanzoug.com\/file\/?VTNSbAk4ATBUXVFpU2ZVOVFuBT0FNwR1B3kAbgJwVSAJfQIzDHdUZgc4BD4LOlIMBjVSM1Y0AjUENVNjXDlbYlViUj0JYAFzVGRRdFM6VWVRPgUwBWwEMQc3ADICalVyCXkCJQxsVDIHYQRgC25SfAZtUmZWJgIyBDJTelwxWztVMVJnCW0BMFQxUWRTY1VjUT0FYwU+BDEHNwAwAj9VYAk7Am0MY1Q4B2EEYQtrUmoGbVJjVj4CYQRmUzNcLlsuVSFSYwlyASBUcVFiU3VVPVFvBT0FYwQ0BzIAPgJuVWwJLwIhDDhUbQc0BDQLYlJiBmpSYFY+AjIEN1NnXDZbb1VnUiMJMgE5VHVROlM2VWJRPAU3BWsEMwcyADMCalVtCS8CIAwhVHcHbARjC2lSYAZrUmNWPwI1BDtTYVw1W3xVIFJsCSQBaFQzUTBTNFV6UTMFMgV0BDQHMgAzAnBVZQk5AmIMd1RkBz0Ebwtv"
    }
  ]
}
~~~

|code| 返回值|
| ------ | ------ |
| 200 | 解析成功 |
| 201 | 链接失效/文件取消分享了/文件不存在或已删除等错误 |
| 202 | 请输入密码 |
| 203 | 解析系统出现问题 |
