<?php
/**
 * Zf library
 *
 * @category    Zf
 * @package     Zf_Util
 * @author      Federico Cargnelutti <fedecarg@gmail.com>
 * @version     $Id: $
 */

/**
 * @category    Zf
 * @package     Zf_Util
 * @author      Federico Cargnelutti <fedecarg@gmail.com>
 * @version     $Id: $
 */
class Zf_Util_String
{
    /**
     * Remove any not alphanumeric char from a string.
     *
     * @param string $string
     * @return string
     */
    public function filter($string, $allowSpaces = true)
    {
        $whiteSpace = $allowSpaces ? '\s' : '';
        return preg_replace('/[^a-z0-9-_'.$whiteSpace.']/i', '', $string);
    }
    
    /**
     * Shorten a string using elipses
     *
     * @param string $string
     * @param string $length
     * @return string
     */
    public function shorten($string, $length)
    {
        if (strlen($string) > $length) {
            $string = preg_replace('/\s\S*$/', '...', substr($string, 0, $length - 3));
        }
        return $string;
    }
    
    /**
     * Convert a string to Search Engine Firendly (SEF).
     *
     * @param string $string
     * @return string
     */
    public function toSef($string)
    {
        $string = $this->filter($string);
        $string = preg_replace('[^a-z0-9.-]', '', str_replace(' ', '-', str_replace('%20', '-', strtolower($string))));
        $string = ltrim(rtrim($string, '-'), '-');
        
        return $string;
    }

    /**
     * Generate a random string
     *
     * @param string Type of random string: alunum, numeric, nozero, unique
     * @param integer Number of characters
     * @return string
     */
    public function random($type = 'alnum', $len=8)
    {
        switch($type) {
            case 'alnum':
            case 'numeric':
            case 'nozero':
                switch ($type) {
                    case 'alnum': 
                        $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                        break;
                    case 'numeric': 
                        $pool = '0123456789';
                        break;
                    case 'nozero':
                        $pool = '123456789';
                        break;
                }

                $str = '';
                for ($i=0; $i < $len; $i++) {
                    $str .= substr($pool, mt_rand(0, strlen($pool) -1), 1);
                }
                return $str;
                break;
            case 'unique': 
                return md5(uniqid(mt_rand()));
                break;
        }
    }
    
    public function encrypt($string, $key)
    {
        $result = '';
        for($i = 0; $i < strlen($string); $i++) {
            $char = substr($string, $i, 1);
            $keychar = substr($key, ($i % strlen($key))-1, 1);
            $char = chr(ord($char)+ord($keychar));
            $result .= $char;
        }

        return base64_encode($result . '||' . $key);
    }

    public function decrypt($string, $key)
    {
        $result = '';
        $stringDecode = base64_decode($string);
        list($string, $key) = explode('||', $stringDecode);
        for($i = 0; $i < strlen($string); $i++) {
            $char = substr($string, $i, 1);
            $keychar = substr($key, ($i % strlen($key))-1, 1);
            $char = chr(ord($char)-ord($keychar));
            $result .= $char;
        }

        return $result;
    }
    
    /**
     * Removes any leading/traling slashes from a string:
     * /foo/bar/ becomes foo/bar
     *
     * @param string
     * @return string
     */
    public function trimSlashes($string)
    {
        return preg_replace("|^/*(.+?)/*$|", "\\1", $string);
    }

    /**
     * Convert all the links in a string to HTML.
     *
     * @param string $string
     * @param string $target
     * @return string
     */
    public function linksToHtml($string, $target = '_self')
    {
        $string = eregi_replace('(((f|ht){1}tp://)[-a-zA-Z0-9@:%_\+.~#?&//=]+)', '<a href="\\1" target="' . $target . '">\\1</a>', $string);
        $string = eregi_replace('([[:space:]()[{}])(www.[-a-zA-Z0-9@:%_\+.~#?&//=]+)', '\\1<a href="http://\\2" target="' . $target . '">\\2</a>', $string);

        return $string;
    }
    
    /**
     * Replace words in a string.
     * 
     * @param string $string
     * @param array $words Censoered words
     * @param string $replacement Optional replacement value
     * @return string
     */
    public function replaceWords($string, array $words, $replacement = '')
    {
        foreach ($words as $word) {
            if ('' !== $replacement) {
                $string = preg_replace("/\b(".str_replace('\*', '\w*?', preg_quote($word)).")\b/i", $replacement, $string);
            } else {
                $string = preg_replace("/\b(".str_replace('\*', '\w*?', preg_quote($word)).")\b/ie", "str_repeat('#', strlen('\\1'))", $string);
            }
        }
        
        return $string;
    }
}
