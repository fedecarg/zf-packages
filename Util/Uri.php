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
class Zf_Util_Uri
{
    /**
     * Returns the URI which was given in order to access this page.
     *
     * @param string $requestUri $_SERVER['REQUEST_URI']
     * @return string
     */
    public function getUri()
    {
        $requestUri = $_SERVER['REQUEST_URI'];

        if (!empty($_SERVER['QUERY_STRING'])) {
            $uri = substr($requestUri, 0, strpos($requestUri, '?'));
            $queryString = str_replace(':', '', urldecode($_SERVER['QUERY_STRING']));
            $requestUri = $uri . '?' . $queryString;
        }

        return $requestUri;
    }

    /**
     * Validates an URI or the path and query of a given URI.
     *
     * @param string $uri
     * @param bool $validatePath Validates the path component of the URI
     * @return bool
     */
    public function isValidUri($uri, $validatePath = false)
    {
        if ($validatePath === true) {
            $pattern['path'] = '((/[a-z0-9-_.%~&+]*)*)?';
            $pattern['query'] = '(\?[^? ]*)?' ;
            $urlPattern = "`^" . $pattern['path'] . $pattern['query'] . "$`iU";
        } else {
            $urlPattern = '/^(http|https):\/\/(([A-Z0-9][A-Z0-9_-]*)(\.[A-Z0-9][A-Z0-9_-]*)+)(:(\d+))?/i';
        }

        return (bool) preg_match($urlPattern, $uri);
    }

    /**
     * Returns the absolute pathname of the currently executing script.
     *
     * @return string
     */
    public function getScriptPath($includeFilename = false)
    {
        $script = $_SERVER['SCRIPT_FILENAME'];
        if ($includeFilename) {
            return substr($script, 0 , strrpos($script, DIRECTORY_SEPARATOR));
        }
        
        return $script;
    }

    /**
     * Returns URI scheme component.
     *
     * @return string
     */
    public function getUriScheme()
    {
        $urlScheme = 'http';
        if (isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS'])) {
            $urlScheme .= 's';
        }

        return $urlScheme;
    }

    /**
     * Returns the address of the page (if any) which referred the user
     * agent to the current page.
     *
     * @return string
     */
    public static function getReferer()
    {
        return (!empty($_SERVER['HTTP_REFERER'])) ? $_SERVER['HTTP_REFERER'] : '';
    }

    /**
     * Sets the parameter values.
     *
     * @param array $urlParams Action controller param array
     * @param array $registerArray Key/value pairs, E.g: array(key=>val, key=>val)
     * @return array
     * @see Trex_Module_Abstract::setParamValues()
     */
    public function setParamValues(array $urlParams, array $registerArray)
    {
        // Convert numerical array to associative array
        if (count($registerArray) > 0 && !array_key_exists(0, $registerArray)) {
            $i=0;
            $tmpParams = array();
            foreach ($registerArray as $key => $val) {
                $tmpParams[$key] = (isset($urlParams[$i])) ? $urlParams[$i] : $val;
                $i++;
            }
            $urlParams = $tmpParams;
        }

        $newUrlParams = $urlParams;
        foreach ($registerArray as $paramKey => $paramValue) {
            if (!isset($urlParams[$paramKey])) {
                if ($paramValue === null) {
                    $newUrlParams[$paramKey] = null;
                } elseif (is_numeric($paramValue)) {
                    $newUrlParams[$paramKey] = (int)$paramValue;
                } else {
                    $newUrlParams[$paramKey] = (string)$paramValue;
                }
            }
        }

        return $newUrlParams;
    }

    /**
     * Returns the IP address from which the user is viewing the current page.
     *
     * @return string
     */
    public function getClientIp()
    {
        $clientIp = 'unknown';
        if (!empty($_SERVER['REMOTE_ADDR'])) {
            $clientIp = $_SERVER['REMOTE_ADDR'];
        } elseif (function_exists('apache_getenv')) {
            $clientIp = apache_getenv('REMOTE_ADDR');

            if (empty($clientIp)) {
                if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
                    $clientIp = $_SERVER['HTTP_CLIENT_IP'];
                } else {
                    $clientIp = apache_getenv('HTTP_CLIENT_IP');
                    if (empty($clientIp)) {
                        // The user is sitting behind a proxy
                        $clientIp  = $_SERVER['HTTP_X_FORWARDED_FOR'] . ' (' . $_SERVER['REMOTE_ADDR'] . ')';
                    }
                }
            }
        }

        return $clientIp;
    }

    /**
     * Returns the Host name from which the user is viewing the current page.
     *
     * @return string
     */
    public static function getHostName()
    {
        if (!empty($_SERVER['REMOTE_HOST'])) {
            $hostName = $_SERVER['REMOTE_HOST'];
        } else {
            $hostName = @gethostbyaddr(self::getClientIp());
        }

        return $hostName;
    }

    /**
     * Returns the contents of the User-Agent.
     *
     * @return string
     */
    public static function getUserAgent()
    {
        return (!empty($_SERVER['HTTP_USER_AGENT'])) ? $_SERVER['HTTP_USER_AGENT'] : '';
    }
}
