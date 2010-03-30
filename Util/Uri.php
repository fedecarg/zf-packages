<?php
/**
 * Copyright (c) 2010, Federico Cargnelutti. All rights reserved.
 * 
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 * 
 * 1. Redistributions of source code must retain the above copyright
 *    notice, this list of conditions and the following disclaimer.
 * 2. Redistributions in binary form must reproduce the above copyright
 *    notice, this list of conditions and the following disclaimer in the
 *    documentation and/or other materials provided with the distribution.
 * 3. All advertising materials mentioning features or use of this software
 *    must display the following acknowledgment:
 *    This product includes software developed by Federico Cargnelutti.
 * 4. Neither the name of Federico Cargnelutti nor the names of its contributors 
 *    may be used to endorse or promote products derived from this software without 
 *    specific prior written permission.
 * 
 * THIS SOFTWARE IS PROVIDED BY FEDERICO CARGNELUTTI "AS IS" AND ANY
 * EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL FEDERICO CARGNELUTTI BE LIABLE FOR ANY
 * DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
 * ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * @category    Zf
 * @package     Zf_Util
 * @author      Federico Cargnelutti <fedecarg@gmail.com>
 * @copyright   Copyright (c) 2010 Federico Cargnelutti
 * @license     New BSD License
 * @version     $Id: $
 */

/**
 * @category    Zf
 * @package     Zf_Util
 * @author      Federico Cargnelutti <fedecarg@gmail.com>
 * @copyright   Copyright (c) 2010 Federico Cargnelutti
 * @license     New BSD License
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
