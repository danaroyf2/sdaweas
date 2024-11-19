<?php

//use Ip2Region;

$rootpath='lib/getip2/';
require $rootpath.'vendor/autoload.php';

function getipinfo($ip)
{
    $ip2region = new Ip2Region();
    //$ip = '125.70.98.43';
    //echo PHP_EOL;
    //echo "查询IP：{$ip}" . PHP_EOL;
    $info = $ip2region->btreeSearch($ip);
    return $info;
}
//getipinfo();

