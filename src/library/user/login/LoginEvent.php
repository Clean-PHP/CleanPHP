<?php
/*
 * Copyright (c) 2023. Ankio. All Rights Reserved.
 */

/**
 * Package: library\login
 * Class LoginEvent
 * Created By ankio.
 * Date : 2022/11/26
 * Time : 16:15
 * Description :
 */

namespace library\user\login;

use cleanphp\event\EventListener;

class LoginEvent implements EventListener
{

    /**
     * @inheritDoc
     */
    public function handleEvent(string $event, &$data)
    {


        if ($data['m'] === 'ankio' && $data['c'] === 'login') {

            Login::route($data['a']);
        }

    }
}