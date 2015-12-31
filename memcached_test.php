<?php

// Setup
$itterations = [10, 100, 1000, 10000];
$values = array();
$compressions = ['No' => false, 'Yes' => true ];
$serializers = ['Default' => Memcached::SERIALIZER_PHP,
                'IgBinary' => Memcached::SERIALIZER_IGBINARY,
                'JSON' => Memcached::SERIALIZER_JSON];
$buffers = ['No' => false, 'Yes' => true ];
$hashes = ['Default' => Memcached::HASH_DEFAULT,
           'MD5' => Memcached::HASH_MD5,
           'CRC' => Memcached::HASH_CRC,
           'NV1_64' => Memcached::HASH_FNV1_64,
           'FNV1A_64' => Memcached::HASH_FNV1A_64,
           'HASH_FNV1_32' => Memcached::HASH_FNV1_32,
           'FNV1A_32' => Memcached::HASH_FNV1A_32,
           'MURMUR' => Memcached::HASH_MURMUR,
           ];

foreach ($buffers as $buffer_key => $buffer_value){
    foreach ($serializers as $serializers_key => $serializers_value){
        foreach ($compressions as $compression_key => $compression_value){
            foreach ($hashes as $hash_key => $hash_value){

                // Output the current run settings
                echo "Hash Algorithm: ".$hash_key."\n";
                echo "Buffer Writes: ".$buffer_key."\n";
                echo "Serializer: ".$serializers_key."\n";
                echo "Compression: ".$compression_key."\n";

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
                    $memcached = new Memcached(crc32(time()));
                    $memcached->addServer('localhost', 11211);
                    $memcached->setOption(Memcached::OPT_COMPRESSION, $compression_value);
                    $memcached->setOption(Memcached::OPT_SERIALIZER, $serializers_value);
                    $memcached->setOption(Memcached::OPT_BUFFER_WRITES, $buffer_value);
                    $memcached->setOption(Memcached::OPT_HASH, $hash_value);

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
                echo "\n";
            }
            echo "\n";
        }
        echo "\n";
    }
    echo "\n";
}
