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

namespace aiddroid\FastCGI\Constants;

/**
 * Class Definition
 * @package aiddroid\FastCGI\Constants
 */
abstract class Definition
{
    const VERSION_1 = 1;

    const TYPE_BEGIN_REQUEST = 1;
    const TYPE_ABORT_REQUEST = 2;
    const TYPE_END_REQUEST = 3;
    const TYPE_PARAMS = 4;
    const TYPE_STDIN = 5;
    const TYPE_STDOUT = 6;
    const TYPE_STDERR = 7;
    const TYPE_DATA = 8;
    const TYPE_GET_VALUES = 9;
    const TYPE_GET_VALUES_RESULT = 10;
    const TYPE_UNKNOWN = 11;

    const ROLE_RESPONDER = 1;
    const ROLE_AUTHORIZER = 2;
    const ROLE_FILTER = 3;

    const PROTCOL_STATUS_REQUEST_COMPLETE = 0;
    const PROTCOL_STATUS_CANT_MPX_CONN = 1;
    const PROTCOL_STATUS_OVERLOADED = 2;
    const PROTCOL_STATUS_UNKNOWN_ROLE = 3;
    
    const HEADER_LENGTH = 8;
}