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
class Zf_Util_Date
{
    /**
     * Format a time interval with the requested granularity.
     *
     * @param integer $timestamp The length of the interval in seconds.
     * @param integer $granularity How many different units to display in the string.
     * @return string A translated string representation of the interval.
     */
    public function getInterval($timestamp, $granularity = 2) 
    {
    	$seconds = time() - $timestamp;
        $units = array(
            '1 year|:count years' => 31536000, 
            '1 week|:count weeks' => 604800, 
            '1 day|:count days' => 86400, 
            '1 hour|:count hours' => 3600, 
            '1 min|:count min' => 60, 
            '1 sec|:count sec' => 1);
        $output = '';
        foreach ($units as $key => $value) {
            $key = explode('|', $key);
            if ($seconds >= $value) {
                $count = floor($seconds / $value);
            	$output .= ($output ? ' ' : '');
                $output .= ($count == 1) ? $key[0] : str_replace(':count', $count, $key[1]);
                $seconds %= $value;
                $granularity--;
            }
            if ($granularity == 0) {
                break;
            }
        }
        
        return $output ? $output : '0 sec';
    }
}
