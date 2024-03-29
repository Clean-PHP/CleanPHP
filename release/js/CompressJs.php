<?php
/*******************************************************************************
 * Copyright (c) 2022. Ankio. All Rights Reserved.
 ******************************************************************************/

/**
 * Package: server\optimization\js
 * Class CompressJs
 * Created By ankio.
 * Date : 2023/3/16
 * Time : 12:22
 * Description :
 */

namespace cleanphp\release\js;

use cleanphp\file\File;
use Exception;

class CompressJs
{
    static function compress($file)
    {
        if (str_ends_with($file, ".min.js")) {
            file_put_contents($file, preg_replace('/\s*\/\/# sourceMappingURL=\S+/', "", file_get_contents($file)));
            File::del($file . ".map");
            return;
        }
        $js = file_get_contents($file);

        try {
            file_put_contents($file, (new JavascriptPacker($js, 0, true, false))->pack());
        } catch (Exception $e) {
        }
    }
}