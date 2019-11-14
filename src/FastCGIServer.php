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

use aiddroid\FastCGI\Constants\FastCGI;
use aiddroid\FastCGI\Records\Header;
use aiddroid\FastCGI\Decoders\HeaderDecoder;
use aiddroid\FastCGI\Encoders\HeaderEncoder;
use aiddroid\FastCGI\Records\EndRequest;
use aiddroid\FastCGI\Encoders\EndRequestEncoder;
use Monolog\Logger;

class FastCGIServer extends FastCGI {

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

        $stdoutBeginHeader = new Header(FastCGI::VERSION_1, FastCGI::TYPE_STDOUT, $requestId, $contentLength, $paddingLength, 1);
        $stdoutEndHeader = new Header(FastCGI::VERSION_1, FastCGI::TYPE_STDOUT, $requestId, 0, 0, 1);

        $endRequestHeader = new Header(FastCGI::VERSION_1, FastCGI::TYPE_END_REQUEST, $requestId, FastCGI::HEADER_LENGTH, 0, 1);
        $endRequest = new EndRequest($appStatus, $protocolStatus, '000');

        $binary = HeaderEncoder::encode($stdoutBeginHeader) .
            pack("a{$length}", $data) .
            HeaderEncoder::encode($stdoutEndHeader) .
            HeaderEncoder::encode($endRequestHeader) .
            EndRequestEncoder::encode($endRequest);

        return $binary;
    }

    /**
     * 根据Type值获取对应的名称
     * @param $type
     * @return string
     */
    protected static function getTypeLabel($type) {
        $types = array(
            FastCGI::TYPE_BEGIN_REQUEST => 'FCGI_TYPE_BEGIN_REQUEST',
            FastCGI::TYPE_ABORT_REQUEST => 'FCGI_TYPE_ABORT_REQUEST',
            FastCGI::TYPE_END_REQUEST => 'FCGI_TYPE_END_REQUEST',
            FastCGI::TYPE_PARAMS => 'FCGI_TYPE_PARAMS',
            FastCGI::TYPE_STDIN => 'FCGI_TYPE_STDIN',
            FastCGI::TYPE_STDOUT => 'FCGI_TYPE_STDOUT',
            FastCGI::TYPE_STDERR => 'FCGI_TYPE_STDERR',
            FastCGI::TYPE_DATA => 'FCGI_TYPE_DATA',
            FastCGI::TYPE_GET_VALUES => 'FCGI_TYPE_GET_VALUES',
            FastCGI::TYPE_GET_VALUES_RESULT => 'FCGI_TYPE_GET_VALUES_RESULT',
            FastCGI::TYPE_UNKNOWN => 'FCGI_TYPE_UNKNOWN'
        );

        return isset($types[$type]) ? $types[$type] : false;
    }

    /**
     * 读取请求数据中的params
     * @param $data
     * @return array
     */
    protected static function readParams($data) {
        $params = array();
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

        $params = array();
        $input = $header = null;

        while($start <= $maxLength - FastCGI::HEADER_LENGTH) {
            // 读取头信息(8个字节),用于判断紧接着的数据类型和长度
            $headerData = substr($data, $start, FastCGI::HEADER_LENGTH);
            $header = HeaderDecoder::decode($headerData);

            $messageLength = $header->getContentLength() + $header->getPaddingLength();
            $message = substr($data, $start + FastCGI::HEADER_LENGTH, $messageLength);

            self::log("==== HEADER INFO ===", Logger::INFO);
            self::log("Header Data: " . bin2hex($headerData));
            self::log("version: {$header->getVersion()}");
            self::log("type: {$header->getType()} " . self::getTypeLabel($header->getType()));
            self::log("requestId: {$header->getRequestId()}");
            self::log("contentLength: {$header->getContentLength()}");
            self::log("paddingLength: {$header->getPaddingLength()}");
            self::log("reserved: {$header->getReserved()}");
            self::log("[ MESSAGE BODY LENGTH: {$messageLength} ]");
            self::log("[ MESSAGE: " . urlencode($message) . " ]");

            switch ($header->getType()) {
                // 如果紧接着的数据是BeginRequestBody, 也是8个字节
                case FastCGI::TYPE_BEGIN_REQUEST :
                    $role = unpack('n', $message[0] . $message[1]);
                    $role = $role[1];
                    $flags = ord($message[2]);
                    $reserved = substr($message, 3, 5);

                    self::log("=== BEGIN REQUEST BODY ===");
                    self::log("role:{$role}");
                    self::log("flags:{$flags}");
                    self::log("reserved:{$reserved}");

                    break;
                // 如果紧接着的数据是PARAMS, 则进行解析
                case FastCGI::TYPE_PARAMS :
                    $ps = self::readParams($message);
                    if ($ps) {
                        $params += $ps;
                    }
                    break;
                // 如果紧接着的数据是STDIN, 则进行读取
                case FastCGI::TYPE_STDIN :
                    if ($message) {
                        $input .= $message;
                    }
                    break;
                // TODO 其他类型处理逻辑待补充
                // case ...
            }

            self::log(PHP_EOL . PHP_EOL);

            $start += $messageLength + FastCGI::HEADER_LENGTH;
        }

        return array($header, $params, $input);
    }
}