# PE
![language](https://img.shields.io/badge/language-php7-blue.svg?longCache=true&language=php7) ![license](https://img.shields.io/badge/license-apache2.0-green.svg?longCache=true&license=apache2.0)  
本仓库总共实现了两个功能：体测自动预约、期末理论考题库（有点遗憾体育选课没有做）

<b>之所以使用php，单纯是因为我想复习php</b>  

## 体测自动预约
针对2018年新版体育系统
![screenshot1](https://github.com/ExplosiveBattery/PE/blob/master/README/DeepinScreenshot_select-area_20180521024421.png?raw=true)
![screenshot2](https://github.com/ExplosiveBattery/PE/blob/master/README/DeepinScreenshot_select-area_20180521024437.png?raw=true)
### 老版系统设计思路（other_scripts/scan.php的由来）
先通过other_scripts/scan.php配合Linux的cron对网站进行扫描（体测开放是人为开放），持续三四天就能够推断出“每一天体测预约的开放时间”。之后在cron中设置好大致时间启动PE_reverse.php，每隔一段固定时间（）扫描目标网站，一旦开放预约就自动预约。我弄一个scan.php这么“麻烦”的原因，一是好奇体育学院的人几点中上班，二是想让扫描程序的行为看起来正常点，免得对方发现有人夜里还在不断地登录网站。其实我还应该加上代理的功能，防止服务器ip被封，可是我的代理池还没有搭建，直接使用免费代理我担心过高甚至丢包，而我并不想在代码里面加上对这些的考虑。  
一般是在9:00还不知道9:30附近（说明是人为开启）开，我有点忘了 
### 新版系统设计思路
新版系统直接告诉我们是在8:00开早上的预约，13:00开下午的预约  
在代码里面设置了“紧张时间段”为刚开的5分钟内，这时候每4秒扫一次；之后都是30秒扫一次  
### 相关内容
- 功能使用界面(没有把css弄的很好看，但是还行): reserve.html
- 记录用户的预约请求: record.php
- 固定时间段预约扫描： reverse.php
- 取消预约: cancle.php
- 各函数具体实现: PE_functions.php 

### 可补充功能
- reverse.php中短信功能完成
- 所有请求加上代理

### 补充说明
虽然将代码已经完全改成适应新版体测系统了，但是小部分代码实际执行效果我还没有验证，我的意思是可能有bug：
- PE_functions.php中的reserve函数
- reverse.php与cron配合以后实际执行效果

如果以后系统没有变化，那么改变js/form.js和record.php中关于时间的硬编码部分即可

## 体育考试期末题库

### 相关内容
爬虫: other_scripts/xxx.py（爬虫本来就不应该用php）
数据库: 暂时没有放上来
功能页: exam.html
后台数据库查询: exam.php

### 可补充功能
我是直接基于数据库的%，进行题目模糊匹配。倒是可以将搜索引擎弄的更加智能，思路是通过NLP中文分词提取出每一个问题的关键词，引擎算法完全可以使用最简单的tf-idf。但是体育学院在线期末考试的题目是可以直接复制的，所以还是直接我这样方便。
