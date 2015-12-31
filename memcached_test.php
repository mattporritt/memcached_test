<?php

$itterations = [10, 100, 1000, 10000];
$values = array();

// Print table header
$cellsize  = 12;
printf("%-{$cellsize}s|", 'Itterations');
printf("%-{$cellsize}s|", 'Set Time');
printf("%-{$cellsize}s|", 'Get Time');
printf("%-{$cellsize}s|", 'Miss Time');
printf("%-{$cellsize}s|", 'Delete Time');
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
    printf("%-{$cellsize}s|", $itteration);
    printf("%-{$cellsize}s|", $set_time);
    printf("%-{$cellsize}s|", $get_time);
    printf("%-{$cellsize}s|", $miss_time);
    printf("%-{$cellsize}s|", $del_time);
    echo "\n";

    // teardown Memcached connection
    $memcached->flush();
    $memcached->quit();
    unset($memcached);
}
