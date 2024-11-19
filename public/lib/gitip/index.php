<?php

use GeoIp2\Database\Reader;
function get_ip_location($ip = ''){
    if (empty($ip)) return [];
    //import('autoload',EXTEND_PATH.'GeoIP2/vendor');
    $rootpath='lib/gitip/';
    require $rootpath.'GeoIP2/vendor/autoload.php';
    // This creates the Reader object, which should be reused across
    // lookups.
    //$reader = new Reader(EXTEND_PATH.'GeoIP2/maxmind-db/city_20180703/GeoLite2-City.mmdb');
    $reader = new Reader($rootpath.'GeoIP2/maxmind-db/city_20180703/GeoLite2-City.mmdb');
    $return = [];
    // Replace "city" with the appropriate method for your database, e.g.,
    // "country".
    $record = $reader->city($ip);
    $return['isoCode'] = $record->country->isoCode;
    $return['country_name'] = $record->country->name;
    $return['cn_country_name'] = $record->country->names['zh-CN'];
    $return['zones_name'] = $record->mostSpecificSubdivision->name;
    $return['zones_ios'] = $record->mostSpecificSubdivision->isoCode;
    $return['city_name'] = $record->city->name;
    $return['city_code'] = $record->postal->code;
    $return['latitude'] = $record->location->latitude;
    $return['longitude'] = $record->location->longitude;

    return $return;

}
//$a=get_ip_location('125.70.98.43');
//var_dump($a);