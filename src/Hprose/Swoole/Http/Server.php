<?php
/**********************************************************\
|                                                          |
|                          hprose                          |
|                                                          |
| Official WebSite: http://www.hprose.com/                 |
|                   http://www.hprose.org/                 |
|                                                          |
\**********************************************************/

/**********************************************************\
 *                                                        *
 * Hprose/Swoole/Http/Server.php                          *
 *                                                        *
 * hprose swoole http server library for php 5.3+         *
 *                                                        *
 * LastModified: Jul 22, 2016                             *
 * Author: Ma Bingyao <andot@hprose.com>                  *
 *                                                        *
\**********************************************************/

namespace Hprose\Swoole\Http;

use stdClass;
use Exception;
use swoole_http_server;

class Server extends Service {
    private function parseUrl($uri) {
        $result = new stdClass();
        $p = parse_url($uri);
        if ($p) {
            switch (strtolower($p['scheme'])) {
                case 'http':
                    $result->host = $p['host'];
                    $result->port = isset($p['port']) ? $p['port'] : 80;
                    break;
                case 'https':
                    $result->host = $p['host'];
                    $result->port = isset($p['port']) ? $p['port'] : 443;
                    break;
                default:
                    throw new Exception("Can't support this scheme: {$p['scheme']}");
            }
        }
        else {
            throw new Exception("Can't parse this uri: $uri");
        }
        return $result;
    }
    public function __construct($uri, $mode = SWOOLE_BASE) {
        parent::__construct();
        $url = $this->parseUrl($uri);
        $this->server = new swoole_http_server($url->host, $url->port, $mode);
    }
    public function on($name, $callback) {
        $this->server->on($name, $callback);
    }
    public function addListener($uri) {
        $url = $this->parseUrl($uri);
        $this->server->addListener($url->host, $url->port);
    }
    public function listen($host, $port, $type) {
        return $this->server->listen($host, $port, $type);
    }
    public function start() {
        if (is_array($this->settings) && !empty($this->settings)) {
            $this->server->set($this->settings);
        }
        $this->httpHandle($this->server);
        $this->server->start();
    }
}
