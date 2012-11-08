#!/usr/bin/env php
<?php
/**
 * Memcached stresstest tool
 *
 * This tool can be used to test the PHP memcached client library and the
 * memcached daemon.
 *
 * Usage: test_memcached.php <host> <port> <loops>
 *
 * @author      Tim de Pater <code AT trafex DOT nl>
 * @link        http://www.trafex.nl
 * @copyright   Copyright (c) 2012 Tim de Pater
 *
 */
if (count($argv) < 4) {
    printf("usage: %s <host> <port> <loops>\n", $argv[0]);
    exit(1);
}

$opts = array(
    Memcached::OPT_COMPRESSION => false,
    Memcached::OPT_DISTRIBUTION => Memcached::DISTRIBUTION_CONSISTENT,
    Memcached::OPT_BINARY_PROTOCOL => true,
);
$totalTime = 0;
for ($i = 0; $i < intval($argv[3]); $i++) {

    $initTime = microtime(true);
    $mc = new Memcached;

    foreach ($opts as $key => $val) {
        $mc->setOption($key, $val);
    }
    $server = $mc->addServer($argv[1], $argv[2]);
    if (true !== $server) {
        printf("%u\tFailed server\t%s\n", $i, var_export($server, true));
    }
    $initTime = microtime(true) - $initTime;

    $setTime = microtime(true);
    $set = $mc->set('foo_bar', 'test', 0);
    if (true !== $set) {
        printf("%u\tFailed set\t%s\n", $i, var_export($set, true));
    }
    $setTime = microtime(true) - $setTime;

    $getTime = microtime(true);
    $get = $mc->get('foo_bar');
    if ('test' !== $get) {
        printf("%u\tFailed get\t%s\n", $i, var_export($get, true));
    }
    $getTime = microtime(true) - $getTime;

    printf("%u\tinit %.6F\tget %.6F\tset %.6F\n", $i, $initTime, $setTime, $getTime);
    $totalTime += ($initTime + $setTime + $getTime);
    unset($mc, $initTime, $setTime, $getTime);
}

printf("\nDone. Did %u loops in %.6F secs\n", $i, $totalTime);
