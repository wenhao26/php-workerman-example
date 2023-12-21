<?php

use Workerman\Connection\UdpConnection;
use Workerman\Timer;
use Workerman\Worker;

require_once '../vendor/autoload.php';

global $clientConnArr;

function handleMessage(UdpConnection $connection, $data)
{
    if (empty($clientConnArr)) {
        $clientConnArr[] = $connection;
    } else {
        array_push($clientConnArr, $connection);
    }

    var_dump($clientConnArr);
    /*Timer::add(3, function () use ($connection) {
        $connection->send('hello');
    });*/

}

$udpWorker            = new Worker('udp://0.0.0.0:2345');
$udpWorker->count     = 1;
$udpWorker->onMessage = 'handleMessage';

Worker::runAll();