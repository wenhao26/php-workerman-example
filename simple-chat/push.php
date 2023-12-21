<?php

use Workerman\Timer;
use Workerman\Worker;
use Workerman\Connection\TcpConnection;

require_once '../vendor/autoload.php';

// 服务端推送
$wsWorker = new Worker('websocket://0.0.0.0:2345');

// 进程启动后，定时推送数据给客户端
$wsWorker->onWorkerStart = function () use ($wsWorker) {
    Timer::add(5, function () use ($wsWorker) {
        var_dump($wsWorker->connections);

        foreach($wsWorker->connections as $connection) {
            $data = json_encode([
                'time'    => date('Y-m-d, H:i:s', time()),
                'message' => 'Server monitoring push...',
                'count'   => count($wsWorker->connections)
            ]);
            $connection->send($data);
        }
    });
};

$wsWorker::runAll();