<?php
namespace Lostmilky\Snowflake;

use Lostmilky\Locallock\LocalLock;

class Snowflake
{
    public $center_id;              // IDC center id
    public $server_id;              // Server id
    public $start_micro_time;       // Set start micro time

    public $lock;

    public function __construct()
    {
        $this->center_id = intval(config('snowflake.center_id', 1) );
        $this->server_id = intval(config('snowflake.server_id', 1) );
        $this->start_micro_time = intval(config('snowflake.start_micro_time', 0) );

        $this->checkConfig();
        $this->lock = new LocalLock();
    }

    /**
     * Desc: Check env config
     * @throws \Exception
     * @author lostmilky zzyydd520@163.com
     * Date: 2020/9/17 17:58
     */
    public function checkConfig()
    {
        if($this->center_id > 32 || $this->center_id < 1) {
            throw new \Exception('center_id muster between 1 and 32', 501);
        }

        if($this->server_id > 32 || $this->server_id < 1) {
            throw new \Exception('center_id muster between 1 and 32', 501);
        }

        if($this->start_micro_time > $this->getMicroTime() || $this->start_micro_time < 0) {
            throw new \Exception('start_micro_time is out of order', 501);
        }
    }

    /**
     * Desc: Parse snow id
     * @param string $id
     * @param bool $decimal
     * @return array
     * @author lostmilky zzyydd520@163.com
     * Date: 2020/9/17 17:58
     */
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

    /**
     * Desc: Get current micro time
     * @return false|float
     * @author lostmilky zzyydd520@163.com
     * Date: 2020/9/17 18:00
     */
    public function getMicroTime()
    {
        return floor(microtime(true) * 1000);
    }

    /**
     * Desc: Get diff micro time
     * @param $current_micro_time
     * @return int
     * @author lostmilky zzyydd520@163.com
     * Date: 2020/9/17 18:00
     */
    public function getDiffMicroTime($current_micro_time)
    {
        return $current_micro_time - $this->start_micro_time;
    }

    /**
     * Desc: Get snow flake id
     * @return string
     * @throws \Exception
     * @author lostmilky zzyydd520@163.com
     * Date: 2020/9/17 18:01
     */
    public function snId()
    {
        $this->lock->lock('s');
        $key = ftok(__FILE__, "K");
        $shmid = shmop_open($key, 'c', 0644, 18);
        $cache = shmop_read($shmid, 0, 18);
        $arr = explode('-', $cache);
        $current_micro_time = $this->getMicroTime();

        if(2 != count($arr) ) {
            $seq_id = 1000;   // 由于 shmop_write 的特性所以需要默认4位数字
        } else {
            $cache_time = (float)$arr[0];
            $cache_id = $arr[1];
            if ($cache_time > $current_micro_time){
                throw new \Exception('Timestamp is out of order', 500);
            } elseif ($cache_time == $current_micro_time) {
                if($cache_id >= 5095) {
                    while($cache_time == $current_micro_time) {
                        $current_micro_time = $this->getMicroTime();
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
        shmop_write($shmid, $current_micro_time.'-'.$seq_id, 0);
        shmop_close($shmid);

        $this->lock->unlock('s');

        $id = $seq_id - 1000; // 前面由于占位原因是1000开启的，这里取消补偿
        $sid = $this->getDiffMicroTime($current_micro_time);

        return (string) ($sid << 22 | $this->center_id << 17 | $this->server_id << 12 | $id);
    }

}
