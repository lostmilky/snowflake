<?php

return [
    'server_id' => env('SNOWFLAKE_SERVER_ID', '1'),   // int between 1-32
    'center_id' => env('SNOWFLAKE_CENTER_ID', '1'),   // int between 1-32
    'start_micro_time' => env('SNOWFLAKE_START_MICRO_TIME', '0'),  // big int  0-Snowflake::getMicroTime()
];
