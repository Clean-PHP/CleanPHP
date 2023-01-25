<?php


namespace library\ip;

use library\ip\IpParser\IpV6wry;
use library\ip\IpParser\QWry;

/**
 *
 */
define("IP_DATABASE_ROOT_DIR", __DIR__);

/**
 * Class IpLocation
 * @package itbdw\Ip
 */
class IpLocation {
    /**
     * @var
     */
    private static $ipV4Path;
    /**
     * @var
     */
    private static $ipV6Path;

    /**
     * @param $ip
     * @param string $ipV4Path
     * @param string $ipV6Path
     * @return array
     */
    public static function getLocationWithoutParse($ip, string $ipV4Path='', string $ipV6Path=''): array
    {

        //if  ipV4Path 记录位置
        if (strlen($ipV4Path)) {
            self::setIpV4Path($ipV4Path);
        }

        //if  ipV6Path 记录位置
        if (strlen($ipV6Path)) {
            self::setIpV6Path($ipV6Path);
        }

        if (self::isIpV4($ip)) {
            $ins = new QWry();
            $ins->setDBPath(self::getIpV4Path());
            $location = $ins->getIp($ip);
        } else if (self::isIpV6($ip)) {
            $ins = new IpV6wry();
            $ins->setDBPath(self::getIpV6Path());
            $location = $ins->getIp($ip);

        } else {
            $location = [
                'error' => 'IP Invalid'
            ];
        }

        return $location;
    }

    /**
     * @param $ip
     * @param string $ipV4Path
     * @param string $ipV6Path
     * @return array
     */
    public static function getLocation($ip, string $ipV4Path='', string $ipV6Path=''): array
    {
        $location = self::getLocationWithoutParse($ip, $ipV4Path, $ipV6Path);
        if (isset($location['error'])) {
            return $location;
        }
        return StringParser::parse($location);
    }

    /**
     * @param $path
     */
    public static function setIpV4Path($path)
    {
        self::$ipV4Path = $path;
    }

    /**
     * @param $path
     */
    public static function setIpV6Path($path)
    {
        self::$ipV6Path = $path;
    }

    /**
     * @return string
     */
    private static function getIpV4Path(): string
    {
        return self::$ipV4Path ? : self::root('/db/qqwry.dat');
    }

    /**
     * @return string
     */
    private static function getIpV6Path(): string
    {
        return self::$ipV6Path ? : self::root('/db/ipv6wry.db');
    }

    /**
     * @param $ip
     * @return bool
     */
    private static function isIpV4($ip): bool
    {
        return false !== filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4);
    }

    /**
     * @param $ip
     * @return bool
     */
    private static function isIpV6($ip): bool
    {
        return false !== filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6);
    }

    /**
     * @param $filename
     * @return string
     */
    public static function root($filename): string
    {
        return IP_DATABASE_ROOT_DIR . $filename;
    }
}