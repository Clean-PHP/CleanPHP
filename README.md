
<p align="center">
<img src="https://cdn.jsdelivr.net/gh/dreamncn/picBed@master/uPic/2022_05_04_13_33_55_1651642435_1651642435229_EuTStm.png">
</p>

<h3 align="center">🚀 安全高效的开发框架</h3>

<p align="center">
 <img src="https://img.shields.io/badge/Composer-885630?style=for-the-badge&logo=Composer&logoColor=white"/>
 <img src="https://img.shields.io/static/v1?label=licenes&message=MIT&color=important&style=for-the-badge"/>
 <img src="https://img.shields.io/static/v1?label=version&message=3.&color=9cf&style=for-the-badge"/>
 <img src="https://img.shields.io/static/v1?label=php&message=%3E%3D8.0&color=777BB4&style=for-the-badge"/>
</p>

## 简介

​		CleanPHP是一套简洁、安全的PHP Web开发框架。CleanPHP的设计思想基于Android与Vue单页应用开发模式，融合了传统PHP框架开发方案，具有开发速度快、运行速度快、安全性可靠等优势。
## 特性

- 支持Composer
- 支持插件化拓展（定时任务、WebSocket等...)
- 支持多种输出渲染引擎（视图渲染、Json渲染等）
- 支持后台任务，需要使用中间件运行PHP才能生效
- 支持数据库ORM模型
- 面向应用的开发模式
- 支持自动精简打包
- 支持单文件运行
- 支持自动检查代码问题并提供解决方案
- ......

## 文档

[阅读文档](https://cleanphp.ankio.net/)

## 快速上手

硬性要求: PHP版本 >= `8.0`

```shell
git clone https://github.com/dreamncn/CleanPHP
```

### Nginx配置Public目录为运行目录：

```
root			/www/a.com/public;
```

### Nginx伪静态配置

```
if ( $uri ~* "^(.*)\.php$") {    
rewrite ^(.*) /index.php break;  
}	

location / {
  try_files $uri $uri/ /index.php?$query_string;
}

location /ws {
         proxy_pass http://127.0.0.1:4405;
         proxy_read_timeout 300s;
         proxy_send_timeout 300s;
         proxy_http_version 1.1;
        proxy_set_header Host $host;
        # 下面这两行是关键
        proxy_set_header Upgrade '';
        proxy_set_header Connection upgrade;
     }
```



## 开源协议

MIT





































