<?php
/*
 *  Copyright (c) 2023. Ankio. All Rights Reserved.
 */

namespace cleanphp\file;

use cleanphp\exception\WarningException;
use ZipArchive;

class File
{
    public static function del(string $name): bool
    {
        if (!is_dir($name) && !is_file($name)) {
            return false;
        }
        try {
            if (is_file($name)) {
                return unlink($name);
            }


            $handle = opendir($name);
            while (($file = readdir($handle)) !== false) {
                if ($file !== "." && $file !== "..") {
                    $dir = $name . '/' . $file;
                    is_dir($dir) ? self::del($dir) : unlink($dir);
                }
            }
            closedir($handle);
            return rmdir($name);
        } catch (WarningException $exception) {

        }
        return true;
    }

    public static function empty(string $path)
    {
        if (is_dir($path)) {
            $p = scandir($path);
            foreach ($p as $val) {
                if ($val != "." && $val != "..") {
                    $subpath = $path . $val;
                    if (is_dir($subpath)) {
                        self::empty($subpath . '/');
                        rmdir($subpath . '/');
                    } else {
                        unlink($subpath);
                    }
                }
            }
        }
    }

    public static function copy(string $src = '', string $dst = ''): bool
    {
        if (empty($src) || empty($dst)) {
            return false;
        }

        if (is_file($src)) {
            return copy($src, $dst);
        }

        $dir = opendir($src);
        self::mkDir($dst);
        while (false !== ($file = readdir($dir))) {
            if (!str_starts_with($file, ".")) {
                if (is_dir($src . '/' . $file)) {
                    self::copy($src . '/' . $file, $dst . '/' . $file);
                } else {
                    copy($src . '/' . $file, $dst . '/' . $file);
                }
            }
        }
        closedir($dir);

        return true;
    }

    public static function mkDir(string $path, bool $recursive = true): bool
    {
        clearstatcache();
        try {
            if (!empty($path) && !file_exists($path)) {
                return mkdir($path, 0777, $recursive);
            }
        } catch (WarningException $e) {

        }

        return true;
    }

    public static function zip($dir, $dst): void
    {
        $zip = new ZipArchive();
        if ($zip->open($dst, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
            self::addDirectoryToZip($dir, $zip, $dir);
        }
    }

    public static function unzip($src, $dst): bool
    {
        $zip = new ZipArchive();
        if ($zip->open($src) === true) {
            File::mkDir($dst);
            $zip->extractTo($dst);
            $zip->close();
            return true;
        }
        return false;
    }

    public static function traverseDirectory($dir, $callback): void
    {
        if (is_dir($dir)) {
            if ($dh = opendir($dir)) {
                while (($file = readdir($dh)) !== false) {
                    if ($file != '.' && $file != '..' && !str_starts_with($file, ".")) {
                        $path = $dir . DIRECTORY_SEPARATOR . $file;
                        if (is_dir($path)) {
                            self::traverseDirectory($path, $callback);
                        } else {
                            call_user_func($callback, $path);
                        }
                    }
                }
                closedir($dh);
            }
        }
    }

    private static function addDirectoryToZip($path, ZipArchive $zip, $replace): void
    {
        $handler = opendir($path);
        while (($filename = readdir($handler)) !== false) {
            if (!str_starts_with($filename, ".")) {
                if (is_dir($path . "/" . $filename)) {
                    self::addDirectoryToZip($path . "/" . $filename, $zip, $replace);
                } else {
                    $zip->addFile($path . "/" . $filename);
                    $zip->renameName($path . "/" . $filename, str_replace($replace, "", $path) . DIRECTORY_SEPARATOR . $filename);
                }
            }
        }
        closedir($handler);
    }
}
