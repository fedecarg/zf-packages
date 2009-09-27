<?php
/**
 * Zf library
 *
 * @category    Zf
 * @package     Zf_DataSource
 * @subpackage  Dao
 * @author      Federico Cargnelutti <fedecarg@gmail.com>
 * @version     $Id: $
 */

/**
 * @category    Zf
 * @package     Zf_DataSource
 * @subpackage  Dao
 * @author      Federico Cargnelutti <fedecarg@gmail.com>
 * @version     $Id: $
 */
class Zf_DataSource_Dao_Mapper
{
    /**
     * @var array Database fields mapped to object properties 
     */
    protected $_map = array();
    
    /**
     * @var Zf_Domain_Entity
     */
    protected $_entity = null;
    
    /**
     * @var object
     */
    protected $_dao = null;
    
    /**
     * Constructor
     * 
     * @param Zf_Domain_Entity $entity
     * @return void
     */
    public function __construct(Zf_Domain_Entity $entity = null)
    {
        if (null !== $entity) {
            $this->setEntity($entity);
        }
    }
    
    /**
     * Set an instance of Zf_Domain_Entity. 
     *
     * @param Zf_Domain_Entity $entity
     * @return void
     */
    public function setEntity(Zf_Domain_Entity $entity)
    {
        $this->_entity = $entity;
    }
    
    /**
     * Return instance of Zf_Domain_Entity.
     *
     * @return Zf_Domain_Entity
     * @throws Zf_DataSource_Dao_Exception
     */
    public function getEntity()
    {
        if (null === $this->_entity) {
            throw new Zf_DataSource_Dao_Exception('Entity not defined');
        }
        return $this->_entity;
    }
    
    /**
     * Set DAO.
     *
     * @param object $dao
     * @return void
     */
    public function setDao($dao)
    {
        $this->_dao = $dao;
    }
    
    /**
     * Return DAO.
     *
     * @return object
     * @throws Zf_DataSource_Dao_Exception
     */
    public function getDao()
    {
        if (null === $this->_dao) {
            throw new Zf_DataSource_Dao_Exception('DAO not defined');
        }
        return $this->_dao;
    }
    
    /**
     * Set map between database fields and object properties. 
     *
     * @param array $map
     * @return void
     */
    public function setMap(array $map)
    {
        $this->_map = $map;
    }
    
    /**
     * Return map.
     *
     * @return array
     */
    public function getMap()
    {
        return $this->_map;
    }
    
    /**
     * Append fields to the map array.
     *
     * @param array (field => property)
     * @return void
     */
    public function append(array $fields) 
    {
        $this->setMap(array_merge($this->getMap(), $fields));
    }
    
    /**
     * Populate Zf_Domain_Entity properities with the given values of an array.
     *
     * @param array $row
     * @return void
     * @throws Zf_DataSource_Dao_Exception
     */
    public function map(array $row)
    {
    	$entity = $this->getEntity();    	
        foreach ($row as $field => $value) {
        	$map = $this->getMap();
            if (!array_key_exists($field, $map)) {
                throw new Zf_DataSource_Dao_Exception(sprintf('No such field "%s"', $field));
            }
            $property = $map[$field];
            if (!property_exists($entity, $property)) {
                $message = sprintf('Property "%s" not defined in %s', $property, get_class($entity));
                throw new Zf_DataSource_Dao_Exception($message);
            }
            $entity->$property = $value;
        }
        
        return $entity;
    }
    
    /**
     * Returns an array containing all of the fields and values. 
     *
     * @return array
     */
    public function getRow()
    {
        $entity = $this->getEntity();
        $array = array();
        foreach ($this->getMap() as $field => $property) {
        	$array[$field] = $entity->$property;
        }
        
        return $array;
    }
}
