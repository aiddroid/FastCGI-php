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

namespace aiddroid\FastCGI;

use aiddroid\FastCGI\Constants\Definition;
use aiddroid\FastCGI\Records\Header;
use aiddroid\FastCGI\Decoders\HeaderDecoder;
use aiddroid\FastCGI\Encoders\HeaderEncoder;
use aiddroid\FastCGI\Records\EndRequest;
use aiddroid\FastCGI\Encoders\EndRequestEncoder;

class FastCGIServer extends Definition {

    /**
     * 构造二进制响应
     *
     * @param $data
     * @param $requestId
     * @param int $appStatus
     * @param int $protocolStatus
     * @return string
     */
    public static function buildResponse($requestId, $data, $appStatus = 0, $protocolStatus = 0)
    {
        $contentLength = strlen($data);
        $paddingLength = $contentLength % 8;
        $length = $contentLength + $paddingLength;

        $header1 = new Header(FastCGIServer::VERSION_1, FastCGIServer::TYPE_STDOUT, $requestId, $contentLength, $paddingLength, 1);
        $header2 = new Header(FastCGIServer::VERSION_1, FastCGIServer::TYPE_STDOUT, $requestId, 0, 0, 1);

        $header3 = new Header(FastCGIServer::VERSION_1, FastCGIServer::TYPE_END_REQUEST, $requestId, FastCGIServer::HEADER_LENGTH, 0, 1);
        $endRequest = new EndRequest($appStatus, $protocolStatus, '000');

        $binary = HeaderEncoder::encode($header1) .
            pack("a{$length}", $data) .
            HeaderEncoder::encode($header2) .
            HeaderEncoder::encode($header3) .
            EndRequestEncoder::encode($endRequest);

        return $binary;
    }

    /**
     * Get type label by type value
     * @param $type
     * @return string
     */
    protected static function getFCGITypeLabel($type) {
        switch ($type) {
            case FastCGIServer::TYPE_BEGIN_REQUEST:
                return 'FCGI_TYPE_BEGIN_REQUEST';
            case FastCGIServer::TYPE_ABORT_REQUEST:
                return 'FCGI_TYPE_ABORT_REQUEST';
            case FastCGIServer::TYPE_END_REQUEST:
                return 'FCGI_TYPE_END_REQUEST';
            case FastCGIServer::TYPE_PARAMS:
                return 'FCGI_TYPE_PARAMS';
            case FastCGIServer::TYPE_STDIN:
                return 'FCGI_TYPE_STDIN';
            case FastCGIServer::TYPE_STDOUT:
                return 'FCGI_TYPE_STDOUT';
            case FastCGIServer::TYPE_STDERR:
                return 'FCGI_TYPE_STDERR';
            case FastCGIServer::TYPE_DATA:
                return 'FCGI_TYPE_DATA';
            case FastCGIServer::TYPE_GET_VALUES:
                return 'FCGI_TYPE_GET_VALUES';
            case FastCGIServer::TYPE_GET_VALUES_RESULT:
                return 'FCGI_TYPE_GET_VALUES_RESULT';
            case FastCGIServer::TYPE_UNKNOW:
                return 'FCGI_TYPE_UNKNOW';
        }
    }

    /**
     * 读取请求数据中的params
     * @param $data
     * @return array
     */
    protected static function readParams($data) {
        $params = [];
        if (!$data) {
            return $params;
        }

        $offset = 0;
        $maxLength = strlen($data);
        // params结束时, nameLength和valueLength均为0, 即00, 所以要求长度大于2个字节
        while ($offset <= $maxLength - 2) {
            // TODO 键/值长度小于127个字节的情况,只读取1个字节来计算长度, 当键/值大于127个字节时, 则需要读取4个字节计算长度,而不是只读1个
            $headNameByteCount = 1;
            $nameLength = ord(substr($data, $offset, $headNameByteCount));

            $valueNameByteCount = 1;
            $valueLength = ord(substr($data, $offset + $headNameByteCount, $valueNameByteCount));

            $name = substr($data, $offset + $headNameByteCount + $valueNameByteCount, $nameLength);
            $value = substr($data, $offset + $headNameByteCount + $valueNameByteCount + $nameLength, $valueLength);
            if ($name) {
                $params[$name] = $value;
            }

            $offset += $headNameByteCount + $valueNameByteCount + $nameLength + $valueLength;
        }

        return $params;
    }

    /**
     * 解析FastCGI请求
     * @param $data
     * @return array
     * @throws \Exception
     */
    public static function parseRequest($data) {
        $start = 0;
        $maxLength = strlen($data);

        $params = [];
        $input = $header = null;

        while($start <= $maxLength - FastCGIServer::HEADER_LENGTH) {
            // 读取头信息(8个字节),用于判断紧接着的数据类型和长度
            $headerData = substr($data, $start, FastCGIServer::HEADER_LENGTH);
            echo "HEADER=" . bin2hex($headerData) . PHP_EOL;

            $header = HeaderDecoder::decode($headerData);

            $messageLength = $header->getContentLength() + $header->getPaddingLength();
            $message = substr($data, $start + FastCGIServer::HEADER_LENGTH, $messageLength);

            echo "==== HEADER INFO ===" . PHP_EOL;
            echo "version: {$header->getVersion()}" . PHP_EOL;
            echo "type: {$header->getType()} " . self::getFCGITypeLabel($header->getType()) . PHP_EOL;
            echo "requestId: {$header->getRequestId()}" . PHP_EOL;
            echo "contentLength: {$header->getContentLength()}" . PHP_EOL;
            echo "paddingLength: {$header->getPaddingLength()}" . PHP_EOL;
            echo "reserved: {$header->getReserved()}" . PHP_EOL;
            echo "[ MESSAGE BODY LENGTH: {$messageLength} ]" . PHP_EOL;
            echo "[ MESSAGE: " . urlencode($message) . " ]" . PHP_EOL;

            switch ($header->getType()) {
                // 如果紧接着的数据是BeginRequestBody, 也是8个字节
                case FastCGIServer::TYPE_BEGIN_REQUEST :
                    $role = unpack('n', $message[0] . $message[1]);
                    $role = $role[1];
                    $flags = ord($message[2]);
                    $reserved1 = substr($message, 3, 5);

                    echo "=== BEGIN REQUEST BODY ===" . PHP_EOL;
                    var_dump($role, $flags, $reserved1);
                    break;
                // 如果紧接着的数据是PARAMS, 则进行解析
                case FastCGIServer::TYPE_PARAMS :
                    $ps = self::readParams($message);
                    if ($ps) {
                        $params += $ps;
                    }
                    break;
                // 如果紧接着的数据是STDIN, 则进行读取
                case FastCGIServer::TYPE_STDIN :
                    if ($message) {
                        $input .= $message;
                    }
                    break;
                // TODO 其他类型处理逻辑待补充
            }

            echo PHP_EOL . PHP_EOL;

            $start += $messageLength + FastCGIServer::HEADER_LENGTH;
        }

        return [$header, $params, $input];
    }
}