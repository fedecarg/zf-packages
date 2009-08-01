<?php
/**
 * Zf library
 *
 * @category    Zf
 * @package     Zf_Data
 * @author      Federico Cargnelutti <fedecarg@gmail.com>
 * @version     $Id: $
 */

/**
 * @category    Zf
 * @package     Zf_Data
 * @author      Federico Cargnelutti <fedecarg@gmail.com>
 * @version     $Id: $
 */
class Zf_Data_Type
{
    /**
     * Data types
     */
    const TYPE_OBJECT  = 'object';
    const TYPE_INTEGER = 'integer';
    const TYPE_STRING  = 'string';
    const TYPE_ARRAY   = 'array';
    const TYPE_BOOLEAN = 'boolean';
    const TYPE_NULL    = 'null';

    /**
     * Returns the type of the given variable.
     *
     * @param mixed $data Reference to PHP variable containing the data.
     * @return string Returns the data type of $data.
     */
    public static function getType(&$data)
    {
        if (is_array($data)) {
            $dataType = self::TYPE_ARRAY;
        } elseif (is_object($data)) {
            $dataType = self::TYPE_OBJECT;
        } elseif (is_int($data)) {
            $dataType = self::TYPE_INTEGER;
        } elseif (is_null($data)) {
            $dataType = self::TYPE_NULL;
        } elseif (is_bool($data)) {
            $dataType = self::TYPE_BOOLEAN;
        } else {
            $dataType = self::TYPE_STRING;
        }
        return $dataType;
    }
}
