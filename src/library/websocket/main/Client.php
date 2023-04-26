<?php
/*******************************************************************************
 * Copyright (c) 2022. Ankio. All Rights Reserved.
 ******************************************************************************/
/**
 * Package: library\websocket
 * Class Client
 * Created By ankio.
 * Date : 2022/12/16
 * Time : 11:11
 * Description :
 */

namespace library\websocket\main;


use cleanphp\file\Log;
use Exception;
use library\websocket\WebsocketException;
use Throwable;

class Client extends Base {

    const PROTOCOL_WS = 'ws';
    const PROTOCOL_WSS = 'wss';

    const HTTP_HEADER_SEPARATION_MARK = "\r\n";
    const HTTP_HEADER_END_MARK = "\r\n\r\n";


    private $protocol;

    private $host;

    private $port;

    private $path;

    private $sock;

    private $closed = false;

    /**
     * Client constructor.
     * @param string $uri
     * @throws WebsocketException
     */
    public function __construct(string $uri) {
        Log::record("ws_client",$uri);
        $this->parseUri($uri);
        $this->connect();
        $this->handshake();
    }

    /**
     * 解析websocket连接地址
     * @param $uri
     * @throws WebsocketException
     */
    private function parseUri($uri) {
        $uri_data = parse_url($uri);
        if (!$uri_data) {
            throw new WebsocketException('不正确的ws uri格式');
        }
        if ($uri_data['scheme'] != self::PROTOCOL_WS && $uri_data['scheme'] != self::PROTOCOL_WSS) {
            throw new WebsocketException('ws的uri必须是以ws://或wss://开头');
        }
        $this->protocol = $uri_data['scheme'];
        $this->host = $uri_data['host'];

        if ($uri_data['port']) {
            $this->port = (int)$uri_data['port'];
        } else {
            if ($this->protocol == self::PROTOCOL_WSS) {
                $this->port = 443;
            } else {
                $this->port = 80;
            }
        }
        $this->path = $uri_data['path'] ?? '/';
        if (isset($uri_data['query'])) {
            $this->path .= '?' . $uri_data['query'];
        }
        if (isset($uri_data['fragment'])) {
            $this->path .= '#' . $uri_data['fragment'];
        }
    }

    /**
     * 连接websocket服务器
     * @throws WebsocketException
     */
    private function connect() {
        $this->sock = stream_socket_client(
            ($this->protocol == self::PROTOCOL_WSS ? 'ssl://' : 'tcp://') . $this->host . ':' . $this->port,
            $errno,
            $error
        );
        if (!$this->sock) {
            if ($error) {
                throw new WebsocketException('连接ws服务器失败：' . $error);
            }
            throw new WebsocketException('连接ws服务器失败: 未知错误');
        }
    }


    /**
     * @param $data
     * @throws WebsocketException
     */
    private function write($data) {
        if (strlen($data) > self::PACKET_SIZE) {
            $data_pieces = str_split($data, self::PACKET_SIZE);
            foreach ($data_pieces as $piece) {
                $this->writeN($piece);
            }
        } else {
            $this->writeN($data);
        }
    }

    /**
     * 向socket写入N个字节
     * @param $str
     * @throws WebsocketException
     */
    private function writeN($str) {
        $len = strlen($str);
        $length = 0;
        do {
            if ($length > 0) {
                $str = substr($str, $length);
            }
            $n = fwrite($this->sock, $str);
            if ($n === false) {
                throw new WebsocketException('无法发送数据，socket连接已断开？');
            }
            $length += $n;
        } while ($length < $len);
    }


    /**
     * websocket握手
     * @throws WebsocketException
     */
    private function handshake() {
        $upgrade_key = base64_encode(md5(uniqid()));
        $headers = [
            'GET ' . $this->path . ' HTTP/1.1',
            'Host: ' . $this->host . ':' . $this->port,
            'Upgrade: websocket',
            'Connection: Upgrade',
            'Sec-WebSocket-Key: ' . $upgrade_key,
            'Sec-WebSocket-Version: 13',
        ];
        Log::record("ws_client","握手准备");
        $this->write(implode(self::HTTP_HEADER_SEPARATION_MARK, $headers) . self::HTTP_HEADER_END_MARK);
        $response = '';
        $end = false;
        do {
            $str = fread($this->sock, 8192);
            if (strlen($str) == 0) {
                break;
            }
            $response .= $str;
            $end = strpos($response, self::HTTP_HEADER_END_MARK);
        } while ($end === false);

        if ($end === false) {
            throw new WebsocketException('握手失败：握手响应不是标准的http响应');
        }

        $resHeader = substr($response, 0, $end);
        $headers = explode(self::HTTP_HEADER_SEPARATION_MARK, $resHeader);

        if (strpos($headers[0], '101') === false) {
            throw new WebsocketException('握手失败：服务器返回http状态码不是101');
        }
        $hand_accept = false;
        for ($i = 1; $i < count($headers); $i++) {
            list($key, $val) = explode(':', $headers[$i]);
            if (strtolower(trim($key)) == 'sec-websocket-accept') {
                $accept = base64_encode(sha1($upgrade_key . "258EAFA5-E914-47DA-95CA-C5AB0DC85B11", true));
                if (trim($val) != $accept) {
                    throw new WebsocketException('握手失败： sec-websocket-accept值校验失败');
                }
                $hand_accept = true;
                break;
            }
        }
        if (!$hand_accept) {
            throw new WebsocketException('握手失败：缺少sec-websocket-accept http头');
        }
    }



    /**
     * ping服务器
     * @throws Exception
     */
    public function ping()
    {
        $this->write(parent::ping());
    }

    /**
     * 响应服务器的ping
     * @throws Exception
     */
    public function pong() {
        $this->write(parent::pong());
    }

    /**
     * 主动关闭与服务器的连接
     * @return bool
     * @throws Exception
     */
    public function close(): bool
    {
        $frame = $this->packFrame(self::OPCODE_CLOSE, '', true, 1000);

        try {
            $this->write($frame);
            // 主动关闭需要再接收一次对端返回的确认消息
            $wsData = $this->recv();
            if ($wsData->opcode == self::OPCODE_CLOSE) {
                return true;
            }
        } catch (Throwable $e) {
        } finally {
            $this->closed = true;
            stream_socket_shutdown($this->sock, STREAM_SHUT_RDWR);
        }
        return false;
    }

    /**
     * ping服务器失败或服务器响应异常时调用，用于关闭socket资源
     */
    public function abnormalClose() {
        if (!$this->closed && $this->sock) {
            $this->closed = true;
            try {
                stream_socket_shutdown($this->sock, STREAM_SHUT_RDWR);
            } catch (Throwable $e) {
            }
        }
    }

    /**
     * 响应服务器的关闭消息
     * @throws WebsocketException
     */
    protected function replyClosure() {
        $frame = $this->packFrame(self::OPCODE_CLOSE, '', true, 1000);
        $this->write($frame);
        $this->closed = true;
        stream_socket_shutdown($this->sock, STREAM_SHUT_RDWR);
    }

    /**
     * @param string $data 要发送的数据
     * @param int $opCode 发送的数据类型 Client::OPCODE_TEXT_FRAME 或 Client::OPCODE_BINARY_FRAME
     * @param bool $isMask 是否使用掩码，默认使用
     * @throws WebsocketException
     */
    public function push(string $data, int $opCode = self::OPCODE_TEXT_FRAME, bool $isMask = true) {
        if ($opCode != self::OPCODE_TEXT_FRAME && $opCode != self::OPCODE_BINARY_FRAME) {
            $opCode = self::OPCODE_TEXT_FRAME ;
        }
        $this->write($this->packFrame($opCode, $data, $isMask));
    }

    /**
     * @return DataFrame
     * @throws Exception
     */
    public function recv(): DataFrame
    {
        $data_frame = $this->readFrame($this->sock);
        switch ($data_frame->opcode) {
            case self::OPCODE_PING:
                $this->pong();
                break;
            case self::OPCODE_PONG:
                break;
            case self::OPCODE_TEXT_FRAME:
            case self::OPCODE_BINARY_FRAME:
            case self::OPCODE_CLOSE:
                if ($data_frame->fin == 0) {
                    do {
                        $continueFrame = $this->readFrame($this->sock);
                        $data_frame->payload .= $continueFrame->payload;
                    } while ($continueFrame->fin == 0);
                }

                if ($data_frame->opcode == self::OPCODE_CLOSE) {
                    $this->replyClosure();
                }
                break;
            default:
                throw new WebsocketException('无法识别的frame数据');
        }
        return $data_frame;
    }


    /**
     * __destruct
     */
    public function __destruct() {
        $this->abnormalClose();
    }
}

