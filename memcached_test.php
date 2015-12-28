<?php
// Initialize values: 10000 keys of 20 bytes with 40 bytes of data
$c = 10000;
$valuealues = array();

for ($i=0;$i<$c;$i++) {
    $valuealues[sprintf('%020s',$i)]=sha1($i);
}

echo "memcached: $c keys\n";

// Memcached
$memcached = new Memcached(crc32('test'));
$memcached->addServer('localhost', 11211);

// set values
$start = microtime(true);
foreach ($valuealues as $key => $value){
    $memcached->set($key, $value);
}
$time = sprintf('%01.4f', microtime(true) - $start);
echo "memcached set: $time\n";

// get values (hits)
$start = microtime(true);
foreach ($valuealues as $key => $value) {
    $memcached->get($key);
}
$time = sprintf('%01.4f', microtime(true) - $start);
echo "memcached get hit: $time\n";

// get values (miss)
$start = microtime(true);
foreach ($valuealues as $key => $value) {
    $memcached->get('miss'.$key);
}
$time = sprintf('%01.4f', microtime(true) - $start);
echo "memcached get miss: $time\n";

// Delete values
$start = microtime(true);
foreach ($valuealues as $key => $value) {
    $memcached->delete($key);
}
$time = sprintf('%01.4f', microtime(true) - $start);
echo "memcached delete: $time\n";
