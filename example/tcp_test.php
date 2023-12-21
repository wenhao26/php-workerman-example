<?php
use Workerman\Worker;
use Workerman\Connection\TcpConnection;
use Workerman\Protocols\Http\Request;

require_once '../vendor/autoload.php';

// 创建一个Worker监听2345端口，不使用任何应用层协议
$tcpWorker = new Worker('tcp://0.0.0.0:2345');

// 启动4个进程对外提供服务
$tcpWorker->count = 4;

// 当收到客户端发来的数据后返回hello $data给客户端
$tcpWorker->onMessage = function (TcpConnection $connection, $data) {
    $connection->send('hello ' . $data);
};

// 运行Worker
Worker::runAll();