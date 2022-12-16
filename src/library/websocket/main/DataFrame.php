<?php
/*******************************************************************************
 * Copyright (c) 2022. Ankio. All Rights Reserved.
 ******************************************************************************/

/**
 * File DataFrame.php
 * Created By ankio.
 * Date : 2022/12/16
 * Time : 11:11
 * Description :
 */

namespace library\websocket\main;

/**
 * websocket数据帧
 * Class wsDataFrame
 * @package library\util
 */
class DataFrame
{
    /**
     * @var int $opcode
     */
    public int $opcode;

    /**
     * @var int $fin 标识数据包是否已结束
     */
    public int $fin;

    /**
     * @var int $status 关闭时的状态码，如果有的话
     */
    public int $status;

    /**
     * @var string 数据包携带的数据
     */
    public string $payload;


}