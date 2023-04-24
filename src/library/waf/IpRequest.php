<?php
/*******************************************************************************
 * Copyright (c) 2022. Ankio. All Rights Reserved.
 ******************************************************************************/
/**
 * Package: library\waf
 * Class IpRequest
 * Created By ankio.
 * Date : 2023/1/1
 * Time : 15:28
 * Description :
 */

namespace library\waf;

use cleanphp\base\Request;

class IpRequest
{
    public string $method = "";
    public string $path = "";
    public string $query = "";
    public string $headers = "";
    public string $body = "";
    public int $time = 0;
    function __construct(){
        $this->time = time();
        $this->method = $_SERVER["REQUEST_METHOD"];
        $this->path = $_SERVER["REQUEST_URI"];
        $this->query = $_SERVER["QUERY_STRING"];
        $headers = "";
        foreach (Request::getHeaders() as $key => $v){
            $headers.="$key: $v\n";
        }
        $this->headers = $headers;
        $this->body = file_get_contents('php://input');
    }
    function toString(): string
    {
        return "$this->method $this->path$this->query\n$this->headers\n\n$this->body";
    }
}