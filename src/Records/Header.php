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

namespace aiddroid\FastCGI\Records;

/**
 * Class Header
 * @package aiddroid\FastCGI\Records
 */
class Header
{
    protected $version;
    protected $type;
    protected $requestId;
    protected $contentLength;
    protected $paddingLength;
    protected $reserved;

    /**
     * Header constructor.
     * @param $version
     * @param $type
     * @param $requestId
     * @param $contentLength
     * @param $paddingLength
     * @param $reserved
     */
    public function __construct($version, $type, $requestId, $contentLength, $paddingLength, $reserved = null)
    {
        $this->version = $version;
        $this->type = $type;
        $this->requestId = $requestId;
        $this->contentLength = $contentLength;
        $this->paddingLength = $paddingLength;
        $this->reserved = $reserved;
    }

    /**
     * @return mixed
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @param mixed $version
     * @return Header
     */
    public function setVersion($version)
    {
        $this->version = $version;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param mixed $type
     * @return Header
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getRequestId()
    {
        return $this->requestId;
    }

    /**
     * @param mixed $requestId
     * @return Header
     */
    public function setRequestId($requestId)
    {
        $this->requestId = $requestId;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getContentLength()
    {
        return $this->contentLength;
    }

    /**
     * @param mixed $contentLength
     * @return Header
     */
    public function setContentLength($contentLength)
    {
        $this->contentLength = $contentLength;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPaddingLength()
    {
        return $this->paddingLength;
    }

    /**
     * @param mixed $paddingLength
     * @return Header
     */
    public function setPaddingLength($paddingLength)
    {
        $this->paddingLength = $paddingLength;
        return $this;
    }

    /**
     * @return null
     */
    public function getReserved()
    {
        return $this->reserved;
    }

    /**
     * @param null $reserved
     * @return Header
     */
    public function setReserved($reserved)
    {
        $this->reserved = $reserved;
        return $this;
    }
}