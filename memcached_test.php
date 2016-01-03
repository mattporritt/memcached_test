<?php
error_reporting(E_ALL);

// Setup
$itterations = [10,
                100,
                1000,
                10000,
                100000
                ];
$values = array();
$setup_values = array();
$best_set = array();
$best_get = array();
$best_miss = array();
$best_delete = array();

//$buffers = ['No' => false, 'Yes' => true ];
$buffers = ['No' => false,];
$serializers = ['Default' => Memcached::SERIALIZER_PHP,
                'IgBinary' => Memcached::SERIALIZER_IGBINARY,
                'JSON' => Memcached::SERIALIZER_JSON,
                ];
$compressions = ['No' => false, 'Yes' => true ];
$hashes = ['Default' => Memcached::HASH_DEFAULT,
           'MD5' => Memcached::HASH_MD5,
           'CRC' => Memcached::HASH_CRC,
           'NV1_64' => Memcached::HASH_FNV1_64,
           'FNV1A_64' => Memcached::HASH_FNV1A_64,
           'HASH_FNV1_32' => Memcached::HASH_FNV1_32,
           'FNV1A_32' => Memcached::HASH_FNV1A_32,
           'MURMUR' => Memcached::HASH_MURMUR,
           ];

echo "\n"."Testing Memcached configuration combinations"."\n\n";

// All the loops to itterate through the setting combinations
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
                    $memcached = new Memcached('test');
                    if (empty ($memcached->getServerList())){
                        $memcached->addServer('localhost', 11211);
                    }

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
                    $values = array();
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
                    $memcached = null;
                    unset($memcached);

                    // Store the best value for the current number of itterations
                    // Best Set
                    if (!isset($best_set[$itteration]['time'])){
                        $best_set[$itteration]['time'] = $set_time;
                        $best_set[$itteration]['buffer'] = $buffer_key;
                        $best_set[$itteration]['serializer'] = $serializers_key;
                        $best_set[$itteration]['compression'] = $compression_key;
                        $best_set[$itteration]['hash'] = $hash_key;

                    }
                    if ($set_time < $best_set[$itteration]['time']){
                        $best_set[$itteration]['time'] = $set_time;
                        $best_set[$itteration]['buffer'] = $buffer_key;
                        $best_set[$itteration]['serializer'] = $serializers_key;
                        $best_set[$itteration]['compression'] = $compression_key;
                        $best_set[$itteration]['hash'] = $hash_key;
                    }

                    // Best Get
                    if (!isset($best_get[$itteration]['time'])){
                        $best_get[$itteration]['time'] = $get_time;
                        $best_get[$itteration]['buffer'] = $buffer_key;
                        $best_get[$itteration]['serializer'] = $serializers_key;
                        $best_get[$itteration]['compression'] = $compression_key;
                        $best_get[$itteration]['hash'] = $hash_key;

                    }
                    if ($get_time < $best_get[$itteration]['time']){
                        $best_get[$itteration]['time'] = $get_time;
                        $best_get[$itteration]['buffer'] = $buffer_key;
                        $best_get[$itteration]['serializer'] = $serializers_key;
                        $best_get[$itteration]['compression'] = $compression_key;
                        $best_get[$itteration]['hash'] = $hash_key;
                    }

                    // Best Miss
                    if (!isset($best_miss[$itteration]['time'])){
                        $best_miss[$itteration]['time'] = $set_time;
                        $best_miss[$itteration]['buffer'] = $buffer_key;
                        $best_miss[$itteration]['serializer'] = $serializers_key;
                        $best_miss[$itteration]['compression'] = $compression_key;
                        $best_miss[$itteration]['hash'] = $hash_key;

                    }
                   if ($miss_time < $best_miss[$itteration]['time']){
                        $best_miss[$itteration]['time'] = $miss_time;
                        $best_miss[$itteration]['buffer'] = $buffer_key;
                        $best_miss[$itteration]['serializer'] = $serializers_key;
                        $best_miss[$itteration]['compression'] = $compression_key;
                        $best_miss[$itteration]['hash'] = $hash_key;
                    }

                    // Best Delete
                    if (!isset($best_delete[$itteration]['time'])){
                        $best_delete[$itteration]['time'] = $del_time;
                        $best_delete[$itteration]['buffer'] = $buffer_key;
                        $best_delete[$itteration]['serializer'] = $serializers_key;
                        $best_delete[$itteration]['compression'] = $compression_key;
                        $best_delete[$itteration]['hash'] = $hash_key;

                    }
                    if ($del_time < $best_delete[$itteration]['time']){
                        $best_delete[$itteration]['time'] = $del_time;
                        $best_delete[$itteration]['buffer'] = $buffer_key;
                        $best_delete[$itteration]['serializer'] = $serializers_key;
                        $best_delete[$itteration]['compression'] = $compression_key;
                        $best_delete[$itteration]['hash'] = $hash_key;
                    }

                }
                echo "\n";
            }
        }
    }
}

// Output best values
echo "\n"."Memcached best configuration combinations"."\n";
echo "\n"."Output format: time, buffer, serializer, compression, hash"."\n";
// Print table header
$cellsize  = 34;
printf("%-11s|", 'Itterations');
printf("%-{$cellsize}s|", 'Best Set');
printf("%-{$cellsize}s|", 'Best Get');
printf("%-{$cellsize}s|", 'Best Miss');
printf("%-{$cellsize}s|", 'Best Delete');
echo "\n";

foreach ($itterations as $itteration){
    $best_set_string = implode(',', $best_set[$itteration]);
    $best_get_string = implode(',', $best_get[$itteration]);
    $best_miss_string = implode(',', $best_miss[$itteration]);
    $best_delete_string = implode(',', $best_delete[$itteration]);

    printf("%-11s|", $itteration);
    printf("%-{$cellsize}s|", $best_set_string);
    printf("%-{$cellsize}s|", $best_get_string);
    printf("%-{$cellsize}s|", $best_miss_string);
    printf("%-{$cellsize}s|", $best_delete_string);
    echo "\n";
}
