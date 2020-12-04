<?php
/* Prevent direct access */
defined('ABSPATH') or die("You can't access this file directly.");

class aspDebug {

    private static $task = array();
    private static $depth = 0;

    public static function start($process = 'default') {
        if (ASP_DEBUG != 1) return;

        print str_repeat(' ', self::$depth * 3) . $process .' START
';
        self::$task[$process] = microtime(true);
        ++self::$depth;
    }

    public static function stop($process = 'default') {
        if (ASP_DEBUG != 1) return;
        --self::$depth;
        print str_repeat(' ', self::$depth * 3) . $process. " END | runtime: ".number_format(microtime(true) - self::$task[$process], 3, '.', '').'s
';
    }
}