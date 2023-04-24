<?php
/*******************************************************************************
 * Copyright (c) 2022. Ankio. All Rights Reserved.
 ******************************************************************************/

/**
 * Package: server\optimization\common
 * Class EmptyFrame
 * Created By ankio.
 * Date : 2023/3/16
 * Time : 12:40
 * Description :
 */

namespace server\optimization\common;

class EmptyFrame
{
    static function empty($withCli = false)
    {
        $new = dirname(BASE_DIR,) . DIRECTORY_SEPARATOR . "dist" . DIRECTORY_SEPARATOR . "temp";
        FileUtils::copyDir(__DIR__, $new);
        if (!$withCli) {
            FileUtils::del($new . DIRECTORY_SEPARATOR . "server" . DIRECTORY_SEPARATOR . 'cli');
        }
        FileUtils::del($new . DIRECTORY_SEPARATOR . "storage");
        FileUtils::del($new . DIRECTORY_SEPARATOR . "app");
        FileUtils::del($new . DIRECTORY_SEPARATOR . "library");
        FileUtils::mkDir($new . DIRECTORY_SEPARATOR . "app");
        file_put_contents($new . DIRECTORY_SEPARATOR . "app" . DIRECTORY_SEPARATOR . ".gitkeep", '');
        FileUtils::mkDir($new . DIRECTORY_SEPARATOR . "library");
        file_put_contents($new . DIRECTORY_SEPARATOR . "library" . DIRECTORY_SEPARATOR . ".gitkeep", '');
        $fileName = dirname(__DIR__) . DIRECTORY_SEPARATOR . "dist" . DIRECTORY_SEPARATOR . "cleanphp.zip";
        FileUtils::zip($new . DIRECTORY_SEPARATOR, $fileName);
        FileUtils::del($new);
        echo "纯净版已打包到：$fileName";
    }
}