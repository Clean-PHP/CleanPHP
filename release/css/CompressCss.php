<?php

namespace cleanphp\release\css;

use cleanphp\file\File;

class CompressCss
{
    static function compress($file)
    {
        /* remove comments */
        $buffer = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', file_get_contents($file));
        /* remove tabs, spaces, newlines, etc. */
        $buffer = str_replace(array("
", "\r", "\n", "\t", '  ', '    ', '    '), '', $buffer);
        file_put_contents($file, preg_replace( '/\s*\/\/# sourceMappingURL=\S+/',"",$buffer));
        File::del($file.".map");
    }

}