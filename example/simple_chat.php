<?php

use Workerman\Worker;
use Workerman\Connection\TcpConnection;
use Workerman\Protocols\Http\Request;

require_once '../vendor/autoload.php';

$globalUid = 0;

function handleConnection(TcpConnection $connection)
{
    global $wsWorker, $globalUid;

    // 为这个连接分配一个UID
    $connection->uid = ++$globalUid;
}

function handleMessage(TcpConnection $connection, $data)
{
    global $wsWorker;
    foreach ($wsWorker->connections as $conn) {
        $conn->send("user[{$connection->uid}] said: $data");
    }
}

function handleClose($connection)
{
    global $wsWorker;
    foreach ($wsWorker->connections as $conn) {
        $conn->send("user[{$connection->uid}] logout");
    }
}

$wsWorker            = new Worker('websocket://0.0.0.0:2345');
$wsWorker->count     = 1;
$wsWorker->onConnect = 'handleConnection';
$wsWorker->onMessage = 'handleMessage';
$wsWorker->onClose   = 'handleClose';
Worker::runAll();