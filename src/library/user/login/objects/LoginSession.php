<?php
/*
 * Copyright (c) 2023. Ankio. All Rights Reserved.
 */

/**
 * Package: library\login
 * Class LoginObject
 * Created By ankio.
 * Date : 2022/11/26
 * Time : 19:44
 * Description :
 */

namespace library\user\login\objects;

use library\verity\VerityObject;

class LoginSession extends VerityObject
{
    public string $token = "";//登录Token
    public string $device = "";//设备
    public int $refresh_time = 0;
    public $extra = null;//额外的数据

    function getRules(): array
    {
        return [];
    }
}