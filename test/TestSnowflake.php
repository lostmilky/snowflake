<?php
// 测试脚本，建议并发开启多个脚本来看效果
require_once '../vendor/autoload.php';

use Lostmilky\Snowflake\Snowflake;

//$currentMicroTime = Snowflake::getMicroTime();
//$id = Snowflake::snId();
//$snowflakeID = decbin($id);
//
//echo 'id: ', $id, PHP_EOL;
//echo 'snowflakeID: ', $snowflakeID, PHP_EOL;
//echo 'currentMicroTime:', $currentMicroTime, PHP_EOL;
//echo 'parse  MicroTime:', bindec(substr($snowflakeID, 0, -22) ), PHP_EOL;


$a = [];

for ($i=0; $i<1; $i++) {
    $a[] = Snowflake::snId();
}

var_dump($a);

//$time_key = ftok(__FILE__, "A");
//
//$time_id = shmop_open($time_key, 'c', 0644, 18);
//$current_time = Snowflake::getMicroTime();
//var_dump($current_time);
//$id = 1234;
//$current_time = $current_time.'_'.$id;
//
//
//shmop_write($time_id, $current_time, 0);
//$cache_time = shmop_read($time_id, 0, 18);
//shmop_close($time_id);
//var_dump($cache_time);