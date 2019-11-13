# FastCGI PHP Version

FastCGI Server & Client[TODO] by php language.

## How can I use it ?

setup your nginx config in nginx.conf, pass requests to FastCGI port 1234 and reload nginx:
```shell
    server {
        listen       8080 default_server;
        server_name  localhost _;
        server_name_in_redirect off;
        root   html;
        index  index.php index.html index.htm;
        
        location /fastcgi {
            fastcgi_pass 127.0.0.1:1234;
            fastcgi_index index.php;
            fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
            include fastcgi_params;
        }
    }
```

download FastCGI-php and run a FastCGI server:
```shell
git clone https://github.com/aiddroid/FastCGI-php.git
cd FastCGI-php
composer install --dev
php tests/workerman-tcp-server.php start
# OR # php tests/php-tcp-server.php
```

now, you will see some information below:
```shell
Workerman[tests/server.php] start in DEBUG mode
----------------------------------------- WORKERMAN -----------------------------------------
Workerman version:3.5.22          PHP version:7.3.11
------------------------------------------ WORKERS ------------------------------------------
proto   user            worker          listen                 processes    status
tcp     allen           none            fCGI://0.0.0.0:1234    1             [OK]
---------------------------------------------------------------------------------------------
Press Ctrl+C to stop. Start success.
```

use curl to test the FastCGI server:
```shell
curl -i "http://localhost:8080/fastcgi?uid=1" --data 'username=allen'
```

and you will see the response:
```shell
HTTP/1.1 200 OK
Server: nginx/1.15.0
Date: Wed, 13 Nov 2019 03:33:52 GMT
Content-Type: text/raw
Transfer-Encoding: chunked
Connection: keep-alive

hello
```

## Authors

* [Allen](https://github.com/aiddroid) (aiddroid@gmail.com)

## License

This project is licensed under the MIT License - for the full copyright and license information, please view the [LICENSE](LICENSE.md) file that was distributed with this source code.

---
_Copyrights (2019) All rights reserved._