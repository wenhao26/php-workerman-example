<?php

use Workerman\Worker;
use Workerman\Connection\TcpConnection;

require_once '../vendor/autoload.php';


function broadcast($message)
{
    global $uidConnections;

    foreach ($uidConnections as $connection) {
        $connection->send($message);
    }

}

$uidConnections = [];
$count          = 0;

$wsWorker        = new Worker('websocket://0.0.0.0:2345');
$wsWorker->count = 1;

$wsWorker->onWorkerStart = function () {
    echo "started...\n";
};

$wsWorker->onConnect = function (TcpConnection $connection) {
    global $count;

    $count++;
};

$wsWorker->onMessage = function (TcpConnection $connection, $data) {
    global $uidConnections, $count;

    // 判断当前客户端是否已经验证，是否设置了UID
    if (!isset($connection->uid)) {
        // 没验证的话把第一个包当做UID（主要为了演示）
        echo "用户[{$data}]上线\n";
        $connection->uid = $data;

        // 保存UID到connection的映射，这样可以通过UID查找connection
        // 实现定向UID推送数据
        $uidConnections[$connection->uid] = $connection;

        $data = json_encode([
            'time'    => date('Y-m-d, H:i:s', time()),
            'message' => 'User [' . $connection->uid . '] online',
            'count'   => $count
        ]);
        broadcast($data);
        return;
    } else {
        echo '[' . $connection->uid . ']:' . $data . "\n";
        $data = json_encode([
            'time'    => date('Y-m-d, H:i:s', time()),
            'message' => '[' . $connection->uid . ']:' . $data,
            'count'   => $count
        ]);
        broadcast($data);
    }
};

$wsWorker->onClose = function (TcpConnection $connection) {
    global $count;

    $count--;
    if (isset($connection->uid)) {
        echo "用户[{$connection->uid}]下线\n";

        // 断开连接时，删除映射
        $data = json_encode([
            'time'    => date('Y-m-d, H:i:s', time()),
            'message' => 'User [' . $connection->uid . '] offline',
            'count'   => $count
        ]);
        broadcast($data);
    }
};

$wsWorker->onError = function (TcpConnection $connection, $code, $message) {
    echo "error:code={$code} - {$message}\n";
};

// 运行Worker
Worker::runAll();