<?php
// Initialize values: 10000 keys of 20 bytes with 40 bytes of data
$c = 10000;
$valuealues = array();

for ($i=0;$i<$c;$i++) {
    $valuealues[sprintf('%020s',$i)]=sha1($i);
}

echo "memcached: $c keys\n";

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

echo "memcached set: $set_time\n";
echo "memcached get hit: $get_time\n";
echo "memcached get miss: $miss_time\n";
echo "memcached delete: $del_time\n";

// teardown Memcached connection
$memcached->flush();
$memcached->quit();
unset($memcached);
