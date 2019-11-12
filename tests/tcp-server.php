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

$host = '127.0.0.1';
$port = '1234';
$callback = 'print';

function receive_tcp_message($host, $port, $callback)
{
    $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);

    // socket_bind() 的参数 $host 必传, 由于是监听本机, 此处可以固定写本机地址
    // 注意: 监听本机地址和内网地址效果不一样
    @socket_bind($socket, $host, $port);
    @set_time_limit(0);

    // 绑定端口之后调用监听函数, 实现端口监听
    @socket_listen($socket, 5);

    // 接下来只需要一直读取, 检查是否有来源连接即可, 如果有, 则会得到一个新的 socket 资源
    while ($child = @socket_accept($socket)) {
        // 休息 1 ms, 也可以不用休息
        usleep(1000);

        // 如果客户端已经断开,则关闭连接
        if (false === socket_getpeername($child, $remote_host, $remote_port)) {
            @socket_close($child);
            continue;
        }

        // 读取请求数据
        // 例如是 http 报文, 则解析 http 报文
        $request = '';
        do {
            $buffer = @socket_read($child, 1024, PHP_BINARY_READ);
            if (false === $buffer) {
                @socket_close($child);
                continue 2;
            }
            $request .= $buffer;
        } while (strlen($buffer) == 1024);

        var_dump($remote_host, $remote_port, $request);

        // 此处省略如何调用 $callback
        $response = $callback($remote_host, $remote_port, $request);
        $response = $response ? $response : 'NO RESPONSE!';

        // 因为是 TCP 链接, 需要返回给客户端处理数据
        $num = 0;
        $length = strlen($response);
        do {
            $buffer = substr($response, $num);
            $ret = @socket_write($child, $buffer);
            $num += $ret;
        } while ($num < $length);

        // 关闭 socket 资源, 继续循环
        @socket_close($child);
    }
}

// 客户端来的任何请求都会打印到屏幕上
receive_tcp_message($host, $port, $callback);
// 如果程序没有出现异常，该进程会一直存在