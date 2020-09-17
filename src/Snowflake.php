<?php
namespace Lostmilky\Snowflake;

use Illuminate\Config\Repository;
use Lostmilky\LocalLock\LocalLock;

class Snowflake
{
    public $center_id = 1;              // IDC 机房 id
    public $server_id = 1;              // 机器 id
    public $start_micro_time = 0;       // 开始时间的毫秒级时间戳
    public $current_micro_time = 0;     // 当前的毫秒级时间戳

    public function __construct(Repository $config)
    {
        $this->config = $config;
        $this->center_id = intval(config('snowflake.center_id') );
        $this->server_id = intval(config('snowflake.server_id') );
        $this->start_micro_time = intval(config('snowflake.start_micro_time') );
        $this->current_micro_time = $this->getMicroTime();

        $this->checkConfig();
    }

    public function checkConfig()
    {
        if($this->center_id > 32 || $this->center_id < 1) {
            throw new \Exception('center_id muster between 1 and 32', 501);
        }

        if($this->server_id > 32 || $this->server_id < 1) {
            throw new \Exception('center_id muster between 1 and 32', 501);
        }

        if($this->start_micro_time > $this->current_micro_time || $this->start_micro_time < 0) {
            throw new \Exception('start_micro_time is out of order', 501);
        }
    }


    public function parseSnId(string $id, $decimal = false): array
    {
        $id = decbin($id);

        $data = [
            'timestamp' => substr($id, 0, -22),
            'seq_id' => substr($id, -12),
            'server_id' => substr($id, -17, 5),
            'center_id' => substr($id, -22, 5),
        ];

        return $decimal ? array_map(function ($value) {
            return bindec($value);
        }, $data) : $data;
    }


    public function getMicroTime()
    {
        return floor(microtime(true) * 1000);
    }


    public function getTimeSequence()
    {
        $this->current_micro_time = $this->getMicroTime();
        return $this->current_micro_time - $this->start_micro_time;
    }


    public function snId()
    {
        LocalLock::lock('s');
        $key = ftok(__FILE__, "K");
        $shmid = shmop_open($key, 'c', 0644, 18);
        $cache = shmop_read($shmid, 0, 18);
        $arr = explode('-', $cache);

        if(2 != count($arr) ) {
            $seq_id = 1000;   // 由于 shmop_write 的特性所以需要默认4位数字
        } else {
            $cache_time = (float)$arr[0];
            $cache_id = $arr[1];
            if ($cache_time > $this->current_micro_time){
                throw new \Exception('Timestamp is out of order', 500);
            } elseif ($cache_time == $this->current_micro_time) {
                if($cache_id >= 5095) {
                    while($cache_time == $this->current_micro_time) {
                        $this->current_micro_time = $this->getMicroTime();
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
        shmop_write($shmid, $this->current_micro_time.'-'.$seq_id, 0);
        shmop_close($shmid);

        LocalLock::unlock('s');

        $id = $seq_id - 1000; // 前面由于占位原因是1000开启的，这里取消补偿
        return (string) ($this->getTimeSequence() << 22 | $this->center_id << 17 | $this->server_id << 12 | $id);
    }


}


