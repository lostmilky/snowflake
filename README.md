# snowflake

### 特点

- 一个基于信号量和共享内存实现的雪花ID生成器，具有很高的效率，而且保证ID不会冲突。


### 运行环境
- Linux
- PHP 7.1.3+

### laravel 安装
```
composer require lostmilky/locallock
```
> laravel 需要修改 app/config/app.php
> 
> providers 里增加如下两行
>
> Lostmilky\Locallock\LocalLockProvider::class,
>
> Lostmilky\Snowflake\SnowflakeProvider::class,

###### Facades 安装（可选）
> 需要修改 app/config/app.php 在 aliases 里增加如下一行
>
> 'Snowflake' => Lostmilky\Snowflake\Facades\Snowflake::class,


### Demo
```
<?php
use Snowflake;

$arr = [];
for ($i=0; $i<500; $i++) {
    $arr[] = Snowflake::snId();
}

foreach ($arr as $id) {
    $s = Snowflake::parseSnId($id, true);
    dump($id.' '. $s['seq_id']);
}

```


### LICENSE

 MIT
