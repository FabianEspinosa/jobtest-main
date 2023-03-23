<?php

function view($file, $data = [], $return = false)
{
    $file = 'src/views/' . $file . '.tpl';

    if (is_file($file)) {
        extract($data);

        ob_start();

        require $file;

        if ($return)
            return ob_get_clean();
        else echo ob_get_clean();
    } else {
        echo $file . ' not exits';
    }
}

/**
 * @param      $link
 * @param null $type
 *
 * @return string
 */
function asset($link, $type = null)
{
    return BASE_URL . 'static/' . (!is_null($type) ? $type . '/' : '') . $link;
}

/**
 * @param        $link
 * @param string $hash
 * @param string $rel
 */
function css($link, $hash = null, $rel = 'stylesheet')
{
    if (strpos($link, 'http') === false) {
        if (is_null($hash) && strpos($link, '?') === false) {
            $link .= '?v=' . filemtime(__DIR__ . DIRECTORY_SEPARATOR . '../../static/css/' . $link);
        }

        $link = asset($link, 'css');
    }

    echo '<link type="text/css" rel="' . $rel . '" href="' . $link . '"/>';
}

/**
 * @param      $link
 */
function js($link)
{
    echo '<script type="text/javascript" src="' . (strpos($link, 'http') !== false ? $link : asset(
        $link,
        'js'
    )) . '"></script>';
}

/**
 * @param $value
 */
function printr($value)
{
    echo '<pre>' . print_r($value, true) . '</pre>';
}

/**
 * @param     $url
 * @param int $status
 */
function redirect($url, $status = 302)
{
    header('Location: ' . str_replace(['&amp;', "\n", "\r"], ['&', '', ''], $url), true, $status);
    exit;
}

/**
 * @param array $data
 * @param int   $code
 */
function json($data = [], $code = 200)
{
    header('Content-Type: application/json');
    http_response_code($code);
    echo json_encode($data);
}

/**
 * @param null|string $route
 * @param array $params
 * @return string
 */
function makeURL($route = null, array $params = [])
{
    $url = BASE_URL;

    return trim(trim(
        str_replace(
            '??',
            '?',
            $url . $route . ($params ? (!MOD_REWRITE ? ($route ? '&' : '') : '?') : '') . http_build_query($params)
        ),
        '?'
    ), '/');
}

function starts_with($haystack, $needle)
{
    $length = strlen($needle);
    return substr($haystack, 0, $length) === $needle;
}

function ends_with($haystack, $needle)
{
    $length = strlen($needle);
    if (!$length) {
        return true;
    }
    return substr($haystack, -$length) === $needle;
}

if (!function_exists('dd')) {
    function dd($obj)
    {
        if (function_exists('dump')) {
            dump($obj);
        } else {
            printr($obj);
        }
    }
}
