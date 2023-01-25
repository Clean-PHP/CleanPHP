<?php



function compressJs($file)
{
    $js = file_get_contents($file);

    try {
        file_put_contents($file, (new JavascriptPacker($js, 0, true, true))->pack());
    } catch (Exception $e) {
    }
}