<?php

class Str {

    protected static $snakeCache = [];
    protected static $camelCache = [];
    protected static $studlyCache = [];

    /**
     * Convert a string to snake case.
     *
     * @param  string  $value
     * @param  string  $delimiter
     * @return string
     */
    public static function snake($value, $delimiter = '_')
    {
        $key = $value.$delimiter;

        if (isset(static::$snakeCache[$key]))
        {
            return static::$snakeCache[$key];
        }

        if ( ! ctype_lower($value))
        {
            $value = strtolower(preg_replace('/(.)(?=[A-Z])/', '$1'.$delimiter, $value));
        }

        return static::$snakeCache[$key] = $value;
    }

    /**
     * Convert a value to camel case.
     *
     * @param  string  $value
     * @return string
     */
    public static function camel($value)
    {
        if (isset(static::$camelCache[$value]))
        {
            return static::$camelCache[$value];
        }

        return static::$camelCache[$value] = lcfirst(static::studly($value));
    }

    /**
     * Convert a value to studly caps case.
     *
     * @param  string  $value
     * @return string
     */
    public static function studly($value)
    {
        $key = $value;

        if (isset(static::$studlyCache[$key]))
        {
            return static::$studlyCache[$key];
        }

        $value = ucwords(str_replace(array('-', '_'), ' ', $value));

        return static::$studlyCache[$key] = str_replace(' ', '', $value);
    }

    /**
     * Generate a more truly "random" alpha-numeric string.
     *
     * @param  int  $length
     * @return string
     *
     * @throws \RuntimeException
     */
    public static function random($length = 16)
    {
        $string = '';

        while (($len = strlen($string)) < $length)
        {
            $size = $length - $len;
            $bytes = static::randomBytes($size);
            $string .= substr(str_replace(['/', '+', '='], '', base64_encode($bytes)), 0, $size);
        }

        return $string;
    }

    /**
     * Generate a more truly "random" bytes.
     *
     * @param  int  $length
     * @return string
     *
     * @throws \RuntimeException
     */
    public static function randomBytes($length = 16)
    {
        if (function_exists('random_bytes'))
        {
            $bytes = random_bytes($length);
        }
        elseif (function_exists('openssl_random_pseudo_bytes'))
        {
            $bytes = openssl_random_pseudo_bytes($length, $strong);
            if ($bytes === false || $strong === false)
            {
                throw new RuntimeException('Unable to generate random string.');
            }
        }
        else
        {
            throw new RuntimeException('OpenSSL extension is required for PHP 5 users.');
        }

        return $bytes;
    }

    /**
     * Limit the number of words in a string.
     *
     * @param  string  $value
     * @param  int     $words
     * @param  string  $end
     * @return string
     */
    public static function words($value, $words = 100, $end = '...')
    {
        preg_match('/^\s*+(?:\S++\s*+){1,'.$words.'}/u', $value, $matches);

        if ( ! isset($matches[0]) || strlen($value) === strlen($matches[0])) return $value;

        return rtrim($matches[0]).$end;
    }

    /**
     * Checks wether a string has a precific beginning.
     *
     * @param   string   $str          string to check
     * @param   string   $start        beginning to check for
     * @param   boolean  $ignore_case  wether to ignore the case
     * @return  boolean  wether a string starts with a specified beginning
     */
    public static function starts_with($str, $start, $ignore_case = false)
    {
        return (bool) preg_match('/^'.preg_quote($start, '/').'/m'.($ignore_case ? 'i' : ''), $str);
    }

    /**
     * Checks wether a string has a precific ending.
     *
     * @param   string   $str          string to check
     * @param   string   $end          ending to check for
     * @param   boolean  $ignore_case  wether to ignore the case
     * @return  boolean  wether a string ends with a specified ending
     */
    public static function ends_with($str, $end, $ignore_case = false)
    {
        return (bool) preg_match('/'.preg_quote($end, '/').'$/m'.($ignore_case ? 'i' : ''), $str);
    }

    /**
     * @param string $value
     * @return bool
     */
    public static function is_serialized($value)
    {
        if (!is_string($value))
        {
            return false;
        }

        if ($value === 'b:0;')
        {
            return true;
        }
        $length	= strlen($value);
        $end	= '';
        if(isset($value[0])) {
            switch ($value[0]) {
                case 's':
                    if ($value[$length - 2] !== '"') {
                        return false;
                    }
                case 'b':
                case 'i':
                case 'd':
                    $end .= ';';
                case 'a':
                case 'O':
                    $end .= '}';
                    if ($value[1] !== ':') {
                        return false;
                    }
                    switch ($value[2]) {
                        case 0:
                        case 1:
                        case 2:
                        case 3:
                        case 4:
                        case 5:
                        case 6:
                        case 7:
                        case 8:
                        case 9:
                            break;
                        default:
                            return false;
                    }
                case 'N':
                    $end .= ';';
                    if ($value[$length - 1] !== $end[0]) {
                        return false;
                    }
                    break;
                default:
                    return false;
            }
        }
        if (($result = @unserialize($value)) === false)
        {
            return false;
        }

        return true;
    }

    /**
     * Check if a string is html
     *
     * @param  string $string string to check
     * @return bool
     */
    public static function is_html($string)
    {
        return strlen(strip_tags($string)) < strlen($string);
    }

    /**
     * Check if a string is json encoded
     *
     * @param  string $string string to check
     * @return bool
     */
    public static function is_json($string)
    {
        if (is_array($string)) {
            return false;
        }

        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }

    /**
     * Check if a string is a valid XML
     *
     * @param  string $string string to check
     * @return bool
     */
    public static function is_xml($string)
    {
        if ( ! defined('LIBXML_COMPACT'))
        {
            throw new Exception('libxml is required to use Str::is_xml()');
        }

        $internal_errors = libxml_use_internal_errors();
        libxml_use_internal_errors(true);
        $result = simplexml_load_string($string) !== false;
        libxml_use_internal_errors($internal_errors);

        return $result;
    }

    /**
     * @param string $str
     * @param array  $replace
     * @param string $delimiter
     * @return string
     */
    public static function to_url($str, $replace = array(), $delimiter = '-') {
        if( !empty($replace) ) {
            $str = str_replace((array)$replace, ' ', $str);
        }

        $clean = preg_replace(array('/Ä/', '/Ö/', '/Ü/', '/ä/', '/ö/', '/ü/'), array('Ae', 'Oe', 'Ue', 'ae', 'oe', 'ue'), $str);
        $clean = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $clean);
        $clean = preg_replace("/[^a-zA-Z0-9\/_|+ -]/", '', $clean);
        $clean = strtolower(trim($clean, '-'));
        $clean = preg_replace("/[\/_|+ -]+/", $delimiter, $clean);

        return $clean;
    }

    public static function fullname_to_names($fullname) {
        $data = [];
        $fullname = array_map('ucfirst', array_map('strtolower', explode(' ', trim($fullname))));

        if (count($fullname) === 1) {
            $data['firstname'] = Arr::head($fullname);
            $data['lastname'] = '';
        } elseif (count($fullname) === 2) {
            $data['firstname'] = Arr::head($fullname);
            $data['lastname'] = Arr::last($fullname);
        } elseif (count($fullname) === 3) {
            $data['firstname'] = Arr::head($fullname);
            $data['lastname'] = implode(' ', array_slice($fullname, 1));
        } elseif (count($fullname) > 3) {
            $data['firstname'] = implode(' ', array_slice($fullname, 0, 2));
            $data['lastname'] = implode(' ', array_slice($fullname, 2));
        }

        return $data;
    }

    public static function fullname_inverse_to_names($fullname) {
        $data = [];
        $fullname = explode(' ', trim($fullname));
        foreach ($fullname as $z => $f) {
            $fullname[$z] = ucfirst(strtolower($f));
        }

        if (strtolower(Arr::head($fullname)) === 'de') {
            $next = 2;
            if (strtolower($fullname[1]) === 'la') {
                $next = 3;
            }
            $fullname = array_merge(
                [implode(' ', array_slice($fullname, 0, $next))],
                array_slice($fullname, $next)
            );
        }

        if (count($fullname) === 1) {
            $data['firstname'] = Arr::head($fullname);
            $data['lastname'] = '';
        } elseif (count($fullname) === 2) {
            $data['firstname'] = Arr::last($fullname);
            $data['lastname'] = Arr::head($fullname);
        } elseif (count($fullname) === 3) {
            $data['firstname'] = Arr::last($fullname);
            $data['lastname'] = implode(' ', array_slice($fullname, 0, 2));
        } elseif (count($fullname) > 3) {
            $data['firstname'] = implode(' ', array_slice($fullname, 2));
            $data['lastname'] = implode(' ', array_slice($fullname, 0, 2));
        }

        return $data;
    }

    public static function get_timestamp($value, $format) {
        $from = $value;
        if (!Str::is_timestamp($value)) {
            if (!is_int($value) && strpos($value, " ") !== false) $format .= " H:i:s";
            $_date = DateTime::createFromFormat($format, $value);
            if ($_date) {
                $from = $_date->getTimestamp();
            } else {
                throw new Exception("NO CONVERT TO DATE");
            }
        }
        return $from;
    }

    /**
     * Checks if a string is a valid timestamp.
     *
     * @param string $timestamp Timestamp to validate.
     *
     * @return bool
     */
    public static function is_timestamp($timestamp) {
        $check = (is_int($timestamp) OR is_float($timestamp))
            ? $timestamp
            : (string)(int)$timestamp;
        return ($check === $timestamp)
            AND ((int)$timestamp <= PHP_INT_MAX)
            AND ((int)$timestamp >= ~PHP_INT_MAX);
    }
}