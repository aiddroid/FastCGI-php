<?php
/**
 * This file is part of fastcgi-php.
 *
 * Copyright (c) 2019 Allen <aiddroid@gmail.com>
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of
 * this software and associated documentation files (the "Software"), to deal in
 * the Software without restriction, including without limitation the rights to
 * use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies
 * of the Software, and to permit persons to whom the Software is furnished to do
 * so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/Protocols/FCGI.php';

use Workerman\Worker;


// #### create socket and listen 1234 port ####
$tcp_worker = new Worker("FCGI://0.0.0.0:1234");
// Emitted when new connection come
$tcp_worker->onConnect = function ($connection) {
    echo "New Connection #{$connection->id}" . PHP_EOL;
};

// Emitted when data received
$tcp_worker->onMessage = function ($connection, $data) {
    echo "===>>> onMessage called! <<<===" . PHP_EOL;
    list($header, $params, $inputData) = $data;

    var_dump($header, $params, $inputData);

    $res = [
        'requestId' => $header->getRequestId(),
        'data' => "Content-Type: text/raw\r\n\r\nhello",
        'appStatus' => 0,
        'protocolStatus' => 0

    ];
    $connection->send($res);
    $connection->close();
};

// Emitted when new connection come
$tcp_worker->onClose = function ($connection) {
    echo "Connection closed #{$connection->id}" . PHP_EOL;
};

Worker::runAll();
