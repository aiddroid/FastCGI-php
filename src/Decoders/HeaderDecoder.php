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

namespace aiddroid\FastCGI\Decoders;


use aiddroid\FastCGI\Constants\FastCGI;
use aiddroid\FastCGI\Records\Header;

/**
 * Class HeaderDecoder
 * @package aiddroid\FastCGI\Decoders
 */
class HeaderDecoder
{

    /**
     * @param $data
     * @return Header
     * @throws \Exception
     */
    public static function decode($data) {
        if (strlen($data) !== FastCGI::HEADER_LENGTH) {
            throw new \Exception("Invalid FastCGI header length.");
        }

        $version = ord($data[0]);
        $type = ord($data[1]);
        $requestId = unpack('n', $data[2] . $data[3]);
        $requestId = $requestId[1];
        $contentLength = unpack('n', $data[4] . $data[5]);
        $contentLength = $contentLength[1];
        $paddingLength = ord($data[6]);
        $reserved = ord($data[7]);

        if ($version !== FastCGI::VERSION_1) {
            throw new \Exception("Unsupported FastCGI version.");
        }

        return new Header(
            $version,
            $type,
            $requestId,
            $contentLength,
            $paddingLength,
            $reserved
        );
    }
}