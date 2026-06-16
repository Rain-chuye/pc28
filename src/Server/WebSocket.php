<?php
// Note: This requires the workerman/workerman package via composer
// composer require workerman/workerman

use Workerman\Worker;
require_once __DIR__ . '/../../vendor/autoload.php';

$ws_worker = new Worker("websocket://0.0.0.0:2345");
$ws_worker->count = 4;

$ws_worker->onConnect = function($connection) {
    echo "New connection\n";
};

$ws_worker->onMessage = function($connection, $data) {
    // Handle incoming messages if needed
};

$ws_worker->onClose = function($connection) {
    echo "Connection closed\n";
};

// Function to broadcast new results
function broadcastNewResult($result) {
    global $ws_worker;
    foreach ($ws_worker->connections as $connection) {
        $connection->send(json_encode([
            'type' => 'new_result',
            'data' => $result
        ]));
    }
}

// Worker::runAll();
