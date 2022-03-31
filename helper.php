<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2022-03-31 19:34:21
 * @modify date 2022-03-31 20:41:49
 * @license GPLv3
 * @desc [description]
 */

function getCurrentUrl($query = [])
{   
    return $_SERVER['PHP_SELF'] . '?' . http_build_query(array_merge(['mod' => $_GET['mod'], 'id' => $_GET['id']], $query));
}

function redirectWithMessage(string $url, string $message, $callback = '')
{
    if (is_callable($callback)) exit($callback($url, $message));

    utility::jsAlert($message);
    sleep(5);
    exit(header("Refresh:0; url={$url}"));
}