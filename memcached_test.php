<?php

$itterations = [10, 100, 1000, 10000];
$values = array();

// Print table header
$cellsize  = 12;
printf("[%0{$cellsize}s]|", 'Itterations');
printf("[%0{$cellsize}s]|", 'Set Time');
printf("[%0{$cellsize}s]|", 'Get Time');
printf("[%0{$cellsize}s]|", 'Miss Time');
printf("[%0{$cellsize}s]|", 'Delete Time');
echo "\n";

foreach ($itterations as $itteration){
    // setup  Memcached connection
    $memcached = new Memcached(crc32('test'));
    $memcached->addServer('localhost', 11211);
    
    // Initialize values
    for ($i=0;$i<$itteration;$i++) {
        $values[sprintf('%020s',$i)]=sha1($i);
    }
    
    // set values
    $start = microtime(true);
    foreach ($values as $key => $value){
        $memcached->set($key, $value);
    }
    $set_time = sprintf('%01.4f', microtime(true) - $start);
    
    // get values (hits)
    $start = microtime(true);
    foreach ($values as $key => $value) {
        $memcached->get($key);
    }
    $get_time = sprintf('%01.4f', microtime(true) - $start);
    
    // get values (miss)
    $start = microtime(true);
    foreach ($values as $key => $value) {
        $memcached->get('miss'.$key);
    }
    $miss_time = sprintf('%01.4f', microtime(true) - $start);
    
    // Delete values
    $start = microtime(true);
    foreach ($values as $key => $value) {
        $memcached->delete($key);
    }
    $del_time = sprintf('%01.4f', microtime(true) - $start);
    
    // Print table values
    printf("[%0{$cellsize}s]|", $c);
    printf("[%0{$cellsize}s]|", $set_time);
    printf("[%0{$cellsize}s]|", $get_time);
    printf("[%0{$cellsize}s]|", $miss_time);
    printf("[%0{$cellsize}s]|", $del_time);
    echo "\n";
    
    // teardown Memcached connection
    $memcached->flush();
    $memcached->quit();
    unset($memcached);
}
