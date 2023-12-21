<?php
use Workerman\Worker;
use Workerman\Connection\TcpConnection;
use Workerman\Protocols\Http\Request;

require_once '../vendor/autoload.php';

// 创建一个Worker监听2345端口，使用http协议通讯
$httpWorker = new Worker('http://0.0.0.0:2345');

// 启动4个进程对外提供服务
$httpWorker->count = 4;

// 接收到浏览器发送数据是回复hello world给浏览器
$httpWorker->onMessage = function (TcpConnection $connection, Request $request) {
    $connection->send('hello world');
};

// 运行Worker
Worker::runAll();