<?php
/*******************************************************************************
 * Copyright (c) 2022. Ankio. All Rights Reserved.
 ******************************************************************************/
/**
 * Package: library\websocket
 * Class Base
 * Created By ankio.
 * Date : 2022/12/16
 * Time : 11:17
 * Description :
 */

namespace library\websocket\main;


use core\file\Log;

class Base
{
    const UUID = '258EAFA5-E914-47DA-95CA-C5AB0DC85B11';

    const PACKET_SIZE = (1 << 15);

    // 还有后续帧
    const OPCODE_CONTINUATION_FRAME = 0;
    // 文本帧
    const OPCODE_TEXT_FRAME = 1;
    // 二进制帧
    const OPCODE_BINARY_FRAME = 2;
    // 关闭连接
    const OPCODE_CLOSE = 8;
    // ping
    const OPCODE_PING = 9;
    // pong
    const OPCODE_PONG = 10;

    const FRAME_LENGTH_LEVEL_1_MAX = 125;
    const FRAME_LENGTH_LEVEL_2_MAX = 65535;

    protected int  $read_position = 0;
    protected string  $read_buffer = "";

    /**
     * @param int $opcode 帧类型
     * @param string $payload 携带的数据
     * @param bool $is_mask 是否使用掩码
     * @param int $status 关闭帧状态
     * @return string
     */
    protected function packFrame(int $opcode, string $payload = '', bool $is_mask = true, int $status = 1000): string
    {
        $first_byte = 0x80 | $opcode;
        if ($is_mask) {
            $second_byte = 0x80;
        } else {
            $second_byte = 0x00;
        }

        $payloadLen = strlen($payload);
        if ($opcode == self::OPCODE_CLOSE) {
            // 协议规定关闭帧必须使用掩码
            $is_mask = true;
            $payload = pack('CC', (($status >> 8) & 0xff), $status & 0xff) . $payload;
            $payloadLen += 2;
        }
        if ($payloadLen <= self::FRAME_LENGTH_LEVEL_1_MAX) {
            $second_byte |= $payloadLen;
            $frame = pack('CC', $first_byte, $second_byte);
        } elseif ($payloadLen <= self::FRAME_LENGTH_LEVEL_2_MAX) {
            $second_byte |= 126;
            $frame = pack('CCn', $first_byte, $second_byte, $payloadLen);
        } else {
            $second_byte |= 127;
            $frame = pack('CCJ', $first_byte, $second_byte, $payloadLen);
        }

        if ($is_mask) {
            $maskBytes = [mt_rand(1, 255), mt_rand(1, 255), mt_rand(1, 255), mt_rand(1, 255)];
            $frame .= pack('CCCC', $maskBytes[0], $maskBytes[1], $maskBytes[2], $maskBytes[3]);
            if ($payloadLen > 0) {
                for ($i = 0; $i < $payloadLen; $i++) {
                    $payload[$i] = chr(ord($payload[$i]) ^ $maskBytes[$i % 4]);
                }
            }
        }
        $frame .= $payload;
        return $frame;
    }


    protected function readFrame($socket): DataFrame
    {
        $first_byte = $this->readCharBuffer($socket);

        $fin = ($first_byte >> 7);
        $opcode = $first_byte & 0x0F;
        $second_byte = $this->readCharBuffer($socket);
        $is_masked = ($second_byte >> 7);
        $data_length = $second_byte & 0x7F;
        if ($data_length == 126) {// 2字节无符号整形
            $data_length = ($this->readCharBuffer($socket) << 8) + $this->readCharBuffer($socket);
        } elseif ($data_length == 127) {
            // 8字节无符号整形
            $data_length = $this->readBuffer(8,$socket);
            $res = unpack('Jlen', $data_length);
            $data_length = $res['len'] ?? (ord($data_length[0]) << 56)
                + (ord($data_length[1]) << 48)
                + (ord($data_length[2]) << 40)
                + (ord($data_length[3]) << 32)
                + (ord($data_length[4]) << 24)
                + (ord($data_length[5]) << 16)
                + (ord($data_length[6]) << 8)
                + ord($data_length[7]);
        }

        $data = '';
        $status = 0;
        if ($data_length > 0) {
            if ($is_masked) {
                // 4字节掩码
                $mask_chars = $this->readBuffer(4,$socket);
                $maskSet = [ord($mask_chars[0]), ord($mask_chars[1]), ord($mask_chars[2]), ord($mask_chars[3])];
                $data = $this->readBuffer($data_length,$socket);
                for ($i = 0; $i < $data_length; $i++) {
                    $data[$i] = chr(ord($data[$i]) ^ $maskSet[$i % 4]);
                }
            } else {
                $data = $this->readBuffer($data_length,$socket);
            }
            if ($opcode == self::OPCODE_CLOSE) {
                $status = (ord($data[0]) << 8) + ord($data[1]);
                $data = substr($data, 2);
            }
        }

        $len = $this->read_position;
        if ($len > 0) {
            $this->read_buffer = substr($this->read_buffer, $len);
            $this->read_position -= $len;
        }

        $dataFrame = new DataFrame();
        $dataFrame->opcode = $opcode;
        $dataFrame->fin = $fin;
        $dataFrame->status = $status;
        $dataFrame->payload = $data;
        return $dataFrame;
    }
    /**
     * ping服务器
     */
    public function ping()
    {
        return $this->packFrame(self::OPCODE_PING, '', true);
    }

    /**
     * 响应服务器的ping
     */
    public function pong() {
        return $this->packFrame(self::OPCODE_PONG, '', true);
    }
    /**
     * 从读取缓冲区中当前位置返回指定长度字符串
     * @param int $len 返回长度
     * @return bool|string
     */
    protected function readBuffer(int $len = 1, $socket = null) {
        $target = $this->read_position + $len;
        while ($target > strlen($this->read_buffer)) {
            if(get_resource_type($socket) === "Socket"){
                socket_set_nonblock($socket);
                socket_recv($socket,$read, self::PACKET_SIZE,0);
            }else{
                $read = fread($socket, self::PACKET_SIZE);
            }
            if ($read) {
                $this->read_buffer .= $read;
            }else{
                break;
            }

        }
        $str = substr($this->read_buffer, $this->read_position, $len);
        $this->read_position += $len;
        return $str;
    }

    /**
     * 返回读取缓冲区当前位置字符的ascii码
     * @param $socket
     * @return int
     */
    private function readCharBuffer($socket): int
    {
        $str = $this->readBuffer(1,$socket);
        return empty($str)?65:ord($str[0]);
    }

}