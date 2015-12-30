<?php
// Initialize values: 10000 keys of 20 bytes with 40 bytes of data
$c = 10000;
$valuealues = array();

for ($i=0;$i<$c;$i++) {
    $valuealues[sprintf('%020s',$i)]=sha1($i);
}

echo "memcached: $c keys\n";

// Print table header
$cellsize  = 12;
printf("[%0{$cellsize}s]|", 'Itterations');
printf("[%0{$cellsize}s]|", 'Set Time');
printf("[%0{$cellsize}s]|", 'Get Time');
printf("[%0{$cellsize}s]|", 'Miss Time');
printf("[%0{$cellsize}s]|", 'Delete Time');
echo "\n";

// setup  Memcached connection
$memcached = new Memcached(crc32('test'));
$memcached->addServer('localhost', 11211);

// set values
$start = microtime(true);
foreach ($valuealues as $key => $value){
    $memcached->set($key, $value);
}
$set_time = sprintf('%01.4f', microtime(true) - $start);

// get values (hits)
$start = microtime(true);
foreach ($valuealues as $key => $value) {
    $memcached->get($key);
}
$get_time = sprintf('%01.4f', microtime(true) - $start);

// get values (miss)
$start = microtime(true);
foreach ($valuealues as $key => $value) {
    $memcached->get('miss'.$key);
}
$miss_time = sprintf('%01.4f', microtime(true) - $start);

// Delete values
$start = microtime(true);
foreach ($valuealues as $key => $value) {
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
