<?php
/*******************************************************************************
 * Copyright (c) 2022. Ankio. All Rights Reserved.
 ******************************************************************************/
/**
 * File Common.php
 * Created By ankio.
 * Date : 2023/1/7
 * Time : 17:46
 * Description :
 */
/**
 * 文件夹删除或者文件删除
 * @param $dirname
 * @return bool
 */
function del($dirname): bool
{
    if (!is_dir($dirname)) {
        if (is_file($dirname))
            return unlink($dirname);
        else
            return false;
    }
    $handle = opendir($dirname); //打开目录
    while (($file = readdir($handle)) !== false) {
        if ($file != '.' && $file != '..') {
            //排除"."和"."
            $dir = $dirname . '/' . $file;
            is_dir($dir) ? del($dir) : unlink($dir);
        }
    }
    closedir($handle);
    return rmdir($dirname);
}


/**
 * 文件夹、文件拷贝
 *
 * @param string $src 来源文件夹、文件
 * @param string $dst 目的地文件夹、文件
 * @return void
 */
function copy_dir(string $src = '', string $dst = '')
{
    if (@is_file($src)) {
        copy($src, $dst);
    }

    if (empty($src) || empty($dst)) {
        return;
    }

    $dir = opendir($src);
    mk_dir($dst);
    while (false !== ($file = readdir($dir))) {
        if (($file != '.') && ($file != '..')) {
            if (is_dir($src . '/' . $file)) {
                copy_dir($src . '/' . $file, $dst . '/' . $file);
            } else {
                copy($src . '/' . $file, $dst . '/' . $file);
            }
        }
    }
    closedir($dir);
}

/**
 * 创建文件夹
 *
 * @param string $path 文件夹路径
 * @param bool $recursive 是否递归创建
 */
function mk_dir(string $path, bool $recursive = true)
{
    clearstatcache();
    if (!is_dir($path)) {
        @mkdir($path, 0777, $recursive);
    }
}


function zip($dir, $dst)
{
    $zip = new ZipArchive();
    if ($zip->open($dst, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
        add_file_to_zip($dir, $zip, $dir); //调用方法，对要打包的根目录进行操作，并将ZipArchive的对象传递给方法
    }
}

function add_file_to_zip($path, ZipArchive $zip, $replace)
{
    $handler = opendir($path); //打开当前文件夹由$path指定。
    while (($filename = readdir($handler)) !== false) {
        if (strpos($filename, ".") !== 0) {//文件夹文件名字为'.'和‘..'，不要对他们进行操作
            if (is_dir($path . "/" . $filename)) {// 如果读取的某个对象是文件夹，则递归
                add_file_to_zip($path . "/" . $filename, $zip, $replace);
            } else { //将文件加入zip对象
                $zip->addFile($path . "/" . $filename);
                $zip->renameName($path . "/" . $filename, str_replace($replace, "", $path) . '/' . $filename);
            }
        }
    }
    @closedir($handler);
}


function get_all_file($dir): array
{
    global $no_check;
    global $basedir;
    $files = array();
    if ($head = opendir($dir)) {
        while (($entry = readdir($head)) !== false) {
            $file = str_replace("//", "/", str_replace($basedir, "", $dir) . '/' . $entry);
            $find = false;
            foreach ($no_check as $v) {
                if (strpos($entry, $v) === 0) {
                    $find = true;
                    break;
                }
            }
            if ($find) continue;
            if (strpos($entry, ".") !== 0) {
                if (is_dir($dir . '/' . $entry)) {
                    $files[$entry] = get_all_file($dir . '/' . $entry);
                } else {
                    $files[] = $dir . '/' . $entry;
                }
            }
        }
    }
    closedir($head);
    return $files;
}

function do_file($fileList, $fnName)
{
    if (is_array($fileList) && sizeof($fileList) != 0) {
        foreach ($fileList as $key => $file) {
            do_file($file, $fnName);
        }
    }
    if (!is_array($fileList) && is_file($fileList)) {
        $fnName($fileList);
    }

}


function scan_dirs($path, $dirs, &$dir_array)
{
    if (!is_array($dirs) || count($dirs) < 1) return;
    foreach ($dirs as $dir) {
        if ($dir === '.' || $dir === '..') continue;
        $dir = $path . DIRECTORY_SEPARATOR . $dir;
        if (is_dir($dir)) {
            array_push($dir_array, $dir);
            scan_dirs($dir, scandir($dir), $dir_array);
        }
    }
}