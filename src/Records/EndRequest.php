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
 * Class EndRequest
 * @package aiddroid\FastCGI\Records
 */
class EndRequest{
    protected $appStatus;
    protected $protocolStatus;
    protected $reserved;

    /**
     * EndRequest constructor.
     * @param $appStatus
     * @param $protocolStatus
     * @param $reserved
     */
    public function __construct($appStatus, $protocolStatus, $reserved)
    {
        $this->appStatus = $appStatus;
        $this->protocolStatus = $protocolStatus;
        $this->reserved = $reserved;
    }

    /**
     * @return mixed
     */
    public function getAppStatus()
    {
        return $this->appStatus;
    }

    /**
     * @param mixed $appStatus
     * @return EndRequest
     */
    public function setAppStatus($appStatus)
    {
        $this->appStatus = $appStatus;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getProtocolStatus()
    {
        return $this->protocolStatus;
    }

    /**
     * @param mixed $protocolStatus
     * @return EndRequest
     */
    public function setProtocolStatus($protocolStatus)
    {
        $this->protocolStatus = $protocolStatus;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getReserved()
    {
        return $this->reserved;
    }

    /**
     * @param mixed $reserved
     * @return EndRequest
     */
    public function setReserved($reserved)
    {
        $this->reserved = $reserved;
        return $this;
    }
}