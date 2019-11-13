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

namespace Workerman\Protocols;

use aiddroid\FastCGI\FastCGIServer;
use Monolog\Handler\PHPConsoleHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Workerman\Connection\ConnectionInterface;

class FCGI implements ProtocolInterface
{

    /**
     * Check the integrity of the package.
     * Please return the length of package.
     * If length is unknow please return 0 that mean wating more data.
     * If the package has something wrong please return false the connection will be closed.
     *
     * @param ConnectionInterface $connection
     * @param string $recv_buffer
     * @return int|false
     */
    public static function input($recv_buffer, ConnectionInterface $connection)
    {
        return strlen($recv_buffer);
    }

    /**
     * Decode package and emit onMessage($message) callback, $message is the result that decode returned.
     *
     * @param ConnectionInterface $connection
     * @param string $recv_buffer
     * @return mixed
     */
    public static function decode($recv_buffer, ConnectionInterface $connection)
    {
        $logger = new Logger('FastCGI');
        $logger->pushHandler(new StreamHandler('php://stdout'));
        FastCGIServer::setLogger($logger);

        $data = FastCGIServer::parseRequest($recv_buffer);
        return $data;
    }

    /**
     * Encode package brefore sending to client.
     *
     * @param ConnectionInterface $connection
     * @param mixed $data
     * @return string
     */
    public static function encode($data, ConnectionInterface $connection)
    {
        $requestId = $data['requestId'];
        $rawData = $data['data'];
        $appStatus = $data['appStatus'];
        $protocolStatus = $data['protocolStatus'];

        return FastCGIServer::buildResponse(
            $requestId,
            $rawData,
            $appStatus,
            $protocolStatus
        );
    }
}