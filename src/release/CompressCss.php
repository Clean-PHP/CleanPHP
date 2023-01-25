<?php
function compressCss($file)
{
    /* remove comments */
    $buffer = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', file_get_contents($file));
    /* remove tabs, spaces, newlines, etc. */
    $buffer = str_replace(array("
", "\r", "\n", "\t", '  ', '    ', '    '), '', $buffer);
    file_put_contents($file, $buffer);
}
