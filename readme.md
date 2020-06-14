# haxibiao/matomo

> haxibiao/matomo 是哈希表matomo的通用封装
> 欢迎大家提交代码或提出建议

## 导语


## 环境要求
1. 如果开启MATOMO_USE_SWOOLE, 需要当前服务器pecl install swoole然后service php-fpm restart

## 安装步骤

1. `composer.json`改动如下：
在`repositories`中添加 vcs 类型远程仓库指向 
`http://code.haxibiao.cn/packages/haxibiao-matomo` 
1. 执行`composer require haxibiao/matomo`
2. 如果不是laravel 5.6以上，需要执行`php artisan matomo:install` 来添加MatomoServiceProvider
3. 需要 pecl instal swoole
4. php artisan matomo:publish  
5. ops/workers/web 下复制一个类似答赚和答妹下的conf文件，借助ops worker.sh 自动运行 php artisan matomo:proxy
6. 完成

### 如何完成更新？
> 远程仓库的composer package发生更新时如何进行更新操作呢？
1. 执行`composer update haxibiao/matomo`


## GQL接口说明

## Api接口说明
