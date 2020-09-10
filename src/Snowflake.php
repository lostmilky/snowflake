<?php
namespace Lostmilky\Snowflake;

use Lostmilky\LocalLock\LocalLock;

class Snowflake
{
    public static $startMicroTime = 0;   // 初始的


    public static function getMicroTime()
    {
        return floor(microtime(true) * 1000);
    }

    public static function getTimeSequence($micro_time)
    {
        return $micro_time - self::$startMicroTime;
    }

    public static function getWorkerId()
    {
        return 1;
    }

    public static function snId()
    {
        LocalLock::lock('s');
        $key = ftok(__FILE__, "K");
        $shmid = shmop_open($key, 'c', 0644, 18);
        $cache = shmop_read($shmid, 0, 18);
        $arr = explode('-', $cache);

        $current_time = self::getMicroTime();
        if(2 != count($arr) ) {
            $seq_id = 1000;   // 由于 shmop_write 的特性所以需要默认4位数字
        } else {
            $cache_time = (float)$arr[0];
            $cache_id = $arr[1];
            if ($cache_time > $current_time){
                throw new \Exception('Timestamp is out of order', 500);
            } elseif ($cache_time == $current_time) {
                if($cache_id >= 5095) {
                    while($cache_time == $current_time) {
                        $current_time = self::getMicroTime();
                    }
                    $seq_id = 1000;
                } else {
                    $seq_id = $cache_id + 1;
                }
            } else {
                $seq_id = 1000;
            }
        }

        // 记录序列ID
        shmop_write($shmid, $current_time.'-'.$seq_id, 0);
        shmop_close($shmid);

        LocalLock::unlock('s');

        $id = $seq_id - 1000; // 前面由于占位原因是1000开启的，这里取消补偿
        return (string) (self::getTimeSequence($current_time) << 22 | self::getWorkerId() << 12 | $id);
    }

    public static function parseSnId(string $id, $transform = false): array
    {
        $id = decbin($id);

        $data = [
            'timestamp' => substr($id, 0, -22),
            'sequence' => substr($id, -12),
            'workerid' => substr($id, -22, 10)
        ];

        return $transform ? array_map(function ($value) {
            return bindec($value);
        }, $data) : $data;
    }
}


