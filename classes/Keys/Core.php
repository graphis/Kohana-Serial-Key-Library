<?php defined('SYSPATH') or die('No direct script access.');

class Keys_Core
{
    protected static $_config = array();

    /**
     * Random
     *
     * Generate a random string. Lots of code borrowed from Kohana
     * text class, "Random" static method.
     *
     */
    public static function random($length = 8, $all_caps = FALSE, $all_lowercase = FALSE, $all_numeric = FALSE)
    {
        // Have we populated the config array?
        if ( empty(self::$_config) )
        {
            self::$_config = Kohana::$config->load('keys');
        }

        // Is this an all capital letter only key?
        if (self::$_config['keys.all_caps'] == TRUE || $all_caps == TRUE)
        {
            $pool = "2345679ACDEFHJKLMNPRSTUVWXYZ";
        }

        // Is this an all lowercase only key?
        if (self::$_config['keys.all_lowercase'] == TRUE || $all_lowercase == TRUE)
        {
            $pool = "2345679acdefhjkmnprstyvwxyz";
        }

        // Is this a numeric only key?
        if (self::$_config['keys.numeric_only'] == TRUE || $all_numeric == TRUE)
        {
            $pool = "123456789";
        }

        // We don't want upper, lower or numeric, give em' a mixed back then.
        // I hate how long this if statement is...
        if (self::$_config['keys.all_caps'] == FALSE && $all_caps == FALSE && self::$_config['keys.all_lowercase'] == FALSE && $all_lowercase == FALSE && self::$_config['keys.numeric_only'] == FALSE && $all_numeric == FALSE)
        {
            $pool = "123456789abcdefghijkmnpqrstuvwxyzABCDEFGHIJKLMNPQRSTUVWXYZ";
        }

        // Remove any instances of the replace CHAR from the pool.
        $pool = str_replace(self::$_config->get('keys.replace_char'), "", $pool);

        // Split the pool into an array of characters
        $pool = str_split($pool, 1);

        // Largest pool key
        $max = count($pool) - 1;

        $str = '';
        for ($i = 0; $i < $length; $i++)
        {
            // Select a random character from the pool and add it to the string
            $str .= $pool[mt_rand(0, $max)];
        }

        return $str;
    }

    /*
     * Generate a key using a custom format
     *
     * @param string $format        Format of the key, the the character (default is "X") will be replaced
     * @param array $exclude      Serials to check against for uniqueness
     */
    public static function generate($format = NULL, $all_caps = FALSE, $all_lowercase = FALSE, $all_numeric = FALSE, $exclude = array())
    {
        // Have we populated the config array?
        if ( empty(self::$_config) )
        {
            self::$_config = Kohana::$config->load('keys');
        }

        // No format specified? Use the config file default
        if ($format == NULL)
        {
            $format = self::$_config->get("keys.default_format");
        }
        else
        {
            // Uh oh, supplied format does not have the replace char
            if ( !strpos($format, self::$_config->get('keys.replace_char') ) )
            {
                $exception_arr = array(
                    ":format"            => $format,
                    ":replace_char" => self::$_config->get('keys.replace_char')
                );
                throw new Kohana_Exception("Your supplied key format, ':format' did not contain your replace character which is, ':replace_char'.", $exception_arr);
            }
        }

        $key                = array();
        $format_length = strlen($format);

        // This do-while-loop generates our key
        do {
            for ($i=0 ; $i < $format_length ; $i++)
            {
                if ( $format[$i] == self::$_config->get('keys.replace_char') )
                {
                    $key[] = self::Random(1, $all_caps, $all_lowercase, $all_numeric);
                }
                else
                {
                    $key[] .= $format[$i];
                }

                if (in_array(implode("",$key), $exclude)) $i--;
            }

        } while( in_array($key, $exclude) );

        return implode("",$key);
    }

}
