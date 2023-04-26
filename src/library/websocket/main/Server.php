<?php
/*******************************************************************************
 * Copyright (c) 2022. Ankio. All Rights Reserved.
 ******************************************************************************/

/**
 * Package: library\websocket\main
 * Class Server
 * Created By ankio.
 * Date : 2022/12/16
 * Time : 11:28
 * Description :
 */

namespace library\websocket\main;

use cleanphp\App;
use cleanphp\base\Variables;
use cleanphp\exception\WarningException;
use cleanphp\file\Log;
use Exception;
use library\websocket\SocketInfo;
use library\websocket\WebsocketException;
use library\websocket\WSEvent;

class Server extends Base
{
    private bool $log = false;//客户端sockets
    private $master;
    /**
     * @var SocketInfo[] $sockets
     */
    private array $sockets = [];
    private ?WSEvent $event_handler = null;

    /**
     * @param string $ip ip地址
     * @param int $port 端口
     * @param bool $log 是否记录日志
     * @param WSEvent|null $event_handler 事件处理器
     * @throws WebsocketException
     */
    public function __construct(string $ip = "127.0.0.1", int $port = 4405, bool $log = false, WSEvent $event_handler = null)
    {
        error_reporting(E_ALL);
        set_time_limit(0);
        ob_implicit_flush();
        $this->log = $log;
        $this->server($ip, $port);
        $this->event_handler = $event_handler;
    }

    /**
     * 启动Websocket链接
     * @param $address string 地址
     * @param $port int 端口
     * @throws WebsocketException
     */
    private function server(string $address, int $port)
    {
        try {
            $this->master = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
            socket_set_option($this->master, SOL_SOCKET, SO_REUSEADDR, 1);
            socket_bind($this->master, $address, $port);
            socket_listen($this->master);
            $this->log("开始监听: $address : $port");
            $this->sockets[0] = new SocketInfo(['resource' => $this->master]);
        } catch (Exception $exception) {
            $error = socket_strerror(socket_last_error());
            throw new WebsocketException($error);
        }
    }

    /**
     * 日志
     * @param $t string 日志内容
     * @return void
     */
    private function log(string $t)
    {//控制台输出
        if ($this->log) {
            Log::record("WebSocket", $t);
        }
    }

    /**
     * 运行Websocket
     * @return void
     * @throws WebsocketException
     */
    public function run()
    {
        file_put_contents(Variables::getCachePath('websocket.lock'), getmypid());
        while (true) {
            if (!file_exists(Variables::getCachePath('websocket.lock'))) {
                App::$debug && Log::record("Tasker", "定时任务进程发生变化，当前进程结束");
                break;
            }
            $write = $except = [];
            $sockets = [];
            foreach ($this->sockets as $item) {
                $sockets[] = $item->resource;
            }

            $read_num = socket_select($sockets, $write, $except, null);
            // select作为监视函数,参数分别是(监视可读,可写,异常,超时时间),返回可操作数目,出错时返回false;
            if (false === $read_num) {
                $this->log(socket_strerror(socket_last_error()));
                break;
            }
            foreach ($sockets as $socket) {
                // 如果可读的是服务器socket,则处理连接逻辑
                if ($socket === $this->master) {
                    $client = socket_accept($this->master);
                    // 创建,绑定,监听后accept函数将会接受socket要来的连接,一旦有一个连接成功,将会返回一个新的socket资源用以交互,如果是一个多个连接的队列,只会处理第一个,如果没有连接的话,进程将会被阻塞,直到连接上.如果用set_socket_blocking或socket_set_noblock()设置了阻塞,会返回false;返回资源后,将会持续等待连接。
                    if (false === $client) {
                        $error = socket_strerror(socket_last_error());
                        throw new WebsocketException($error);
                    } else {
                        $this->connect($client);
                    }
                } else {
                    if (!$this->sockets[(int)$socket]->handshake) {
                        $this->handShake($socket, $this->read($socket) ?? "");
                        $this->event_handler && $this->event_handler->onConnect($this, $this->sockets[(int)$socket]);
                        continue;
                    }
                    $data_frame = $this->readFrame($socket);
                    // 如果可读的是其他已连接socket,则读取其数据,并处理应答逻辑
                    switch ($data_frame->opcode) {
                        case self::OPCODE_PING:
                            $this->pong();
                            break;
                        case self::OPCODE_PONG:
                            break;
                        case self::OPCODE_TEXT_FRAME:
                        case self::OPCODE_BINARY_FRAME:
                            /**
                             * Log::record("data",print_r($data_frame,true));
                             * if ($data_frame->fin == 0) {
                             * do {
                             * $continueFrame = $this->readFrame($socket);
                             * $data_frame->payload .= $continueFrame->payload;
                             * } while ($continueFrame->fin == 0);
                             * }**/
                            $this->event_handler && $this->event_handler->onMsg($this, $data_frame->payload, $this->sockets[(int)$socket]);
                            break;
                        case self::OPCODE_CLOSE:
                            $this->event_handler && $this->event_handler->onClose($this, $this->sockets[(int)$socket]);
                            $this->disconnect($socket);
                            break;
                        default:
                            throw new WebsocketException('无法识别的frame数据');
                    }

                }
            }

        }
    }

    /**
     * 将socket添加到已连接列表,但握手状态留空;
     *
     * @param $socket
     */
    public function connect($socket)
    {
        socket_getpeername($socket, $ip, $port);
        $this->sockets[(int)$socket] = new SocketInfo([
            'resource' => $socket,
            'handshake' => false,
            'ip' => $ip,
            'port' => $port,
        ]);
    }

    /**
     * 执行握手
     * @param $socket resource
     * @param $buffer string 请求数据
     * @return void
     */
    private function handshake($socket, string $buffer): void
    {
        // 获取到客户端的升级密匙
        $line_with_key = substr($buffer, strpos($buffer, 'Sec-WebSocket-Key:') + 18);
        $key = trim(substr($line_with_key, 0, strpos($line_with_key, "\r\n")));
        // 生成升级密匙,并拼接websocket升级头
        $upgrade_key = base64_encode(sha1($key . "258EAFA5-E914-47DA-95CA-C5AB0DC85B11", true));// 升级key的算法
        $upgrade_message = "HTTP/1.1 101 Switching Protocols\r\n";
        $upgrade_message .= "Upgrade: websocket\r\n";
        $upgrade_message .= "Sec-WebSocket-Version: 13\r\n";
        $upgrade_message .= "Connection: Upgrade\r\n";
        $upgrade_message .= "Sec-WebSocket-Accept:" . $upgrade_key . "\r\n\r\n";
        socket_write($socket, $upgrade_message, strlen($upgrade_message));// 向socket里写入升级信息
        $this->sockets[(int)$socket]->handshake = true;
    }

    /**
     * 循环读取
     * @param $socket
     * @return mixed|string|null
     */
    private function read($socket)
    {
        $received_data = null;
        socket_set_nonblock($socket);
        socket_clear_error();
        while (@socket_recv($socket, $buf, 4096, 0) >= 1) {
            $received_data = (isset($received_data)) ? $received_data . $buf : $buf;
        }
        return $received_data;
    }

    /**
     * 客户端关闭连接
     *
     * @param $socket
     *
     */
    private function disconnect($socket)
    {
        unset($this->sockets[(int)$socket]);
    }

    /**
     * 推送给所有客户端
     * @param $msg string
     * @param int $opcode
     * @param bool $is_mask
     * @param int $state
     * @return void
     */
    public function pushAll(string $msg, int $opcode = self::OPCODE_TEXT_FRAME, bool $is_mask = false, int $state = 1000)
    {
        foreach ($this->sockets as $socket) {
            if ($socket->resource == $this->master) {
                continue;
            }
            $this->push($socket->resource, $msg, $opcode, $is_mask, $state);
        }
    }

    /**
     * 推送消息
     * @param $socket resource
     * @param $msg string
     * @param int $opcode
     * @param bool $is_mask
     * @param int $state
     * @return void
     */
    public function push($socket, string $msg, int $opcode = self::OPCODE_TEXT_FRAME, bool $is_mask = false, int $state = 1000)
    {

        $id = (int)$socket;
        if (!isset($this->sockets[$id])) {
            return;
        }
        try {
            App::$debug && Log::record('Websocket', '消息推送：' . $msg);
            $t = $this->packFrame($opcode, $msg, $is_mask, $state);
            socket_write($socket, $t, strlen($t));
        } catch (WarningException $exception) {
            if (isset($this->sockets[$id]))
                unset($this->sockets[$id]);
            Log::record("Exception", $exception->getMessage());
        }
    }

    /**
     * 向指定id推送消息
     * @param int $id
     * @param $msg
     * @param int $opcode
     * @param bool $is_mask
     * @param int $state
     * @return void
     */
    public function pushWithId(int $id, $msg, int $opcode = self::OPCODE_TEXT_FRAME, bool $is_mask = false, int $state = 1000)
    {
        if (isset($this->sockets[$id])) {
            $socket = $this->sockets[$id]->resource;
            $this->push($socket, $msg, $opcode, $is_mask, $state);
        }
    }

    /**
     * 推送消息给所有客户端，除了自己
     * @param string $msg
     * @param $self
     * @param int $opcode
     * @param bool $is_mask
     * @param int $state
     * @return void
     */
    public function pushAllWithoutSelf(string $msg, $self, int $opcode = self::OPCODE_TEXT_FRAME, bool $is_mask = false, int $state = 1000)
    {
        foreach ($this->sockets as $socket) {
            if ($socket->resource == $this->master || $socket->resource == $self || $self == 0) {
                continue;
            }
            $this->push($socket->resource, $msg, $opcode, $is_mask, $state);
        }
    }

}