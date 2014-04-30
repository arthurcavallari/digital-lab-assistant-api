<?php

/**
 * Abstract class that enables creation of PHP enums. All you
 * have to do is extend this class and define some constants.
 * Enum is an object with value from on of those constants 
 * (or from on of superclass if any). There is also
 * __default constat that enables you creation of object
 * without passing enum value.
 *
 * @author Marijan Šuflaj &lt;msufflaj32@gmail.com&gt
 * @link http://php4every1.com
 */
abstract class Enum {

    /**
     * Constant with default value for creating enum object
     */
    const __default = null;
    
    private $value;
	
	private $int_value;
    
    private $strict;
    
    private static $constants = array();
    
    /**
     * Returns list of all defined constants in enum class.
     * Constants value are enum values.
     *
     * @param bool $includeDefault If true, default value is included into return
     * @return array Array with constant values
     */
    public function getConstList($includeDefault = false) {
    
        $class = get_class($this);
    
        if (!array_key_exists($class, self::$constants)) {
            self::populateConstants();
        }
        
        return $includeDefault ? array_merge(self::$constants[__CLASS_], array(
            "__default" => self::__default
        )) : self::$constants[__CLASS_];
    }
	
	public static function isValid($value, $strict = true)
	{
		$class = get_called_class();
		$r = new ReflectionClass($class);
        $constants = $r->getConstants();
        
        $constants = array(
            $class => $constants
        );
        		
		$temp = $constants[$class];

		return (in_array($value, $temp, $strict) || array_key_exists($value, $temp));
	}
    
    /**
     * Creates new enum object. If child class overrides __construct(), 
     * it is required to call parent::__construct() in order for this
     * class to work as expected.
     *
     * @param mixed $initialValue Any value that is exists in defined constants
     * @param bool $strict If set to true, type and value must be equal
     * @throws UnexpectedValueException If value is not valid enum value
     */
    public function __construct($initialValue = null, $strict = true) {
    
        $class = get_class($this);
    
        if (!array_key_exists($class, self::$constants)) {
            self::populateConstants();
        }
        
        if ($initialValue === null) {
            $initialValue = self::$constants[$class]["__default"];
        }
        
        $temp = self::$constants[$class];
        
		
        if (!in_array($initialValue, $temp, $strict) && !array_key_exists($initialValue, $temp)) {
            throw new UnexpectedValueException("Value is not in enum " . $class);
        }
        if(is_int($initialValue))
		{
			$this->value = array_search($initialValue, $temp, $strict);
			$this->int_value = $initialValue;
		}
		else
		{
			$this->value = $initialValue;
			$this->int_value = $temp[$initialValue];
		}
        $this->strict = $strict;
    }
    
    private function populateConstants() {
        
        $class = get_class($this);
        
        $r = new ReflectionClass($class);
        $constants = $r->getConstants();
        
        self::$constants = array(
            $class => $constants
        );
    }
    
    /**
     * Returns string representation of an enum. Defaults to 
     * value casted to string.
     *
     * @return string String representation of this enum's value
     */
    public function __toString() 
	{
        return (string) $this->value;
    }
	
	public function __toInteger() 
	{
        return (int) $this->int_value;
    }
    
    /**
     * Checks if two enums are equal. Only value is checked, not class type also.
     * If enum was created with $strict = true, then strict comparison applies 
     * here also.
     *
     * @return bool True if enums are equal
     */
    public function equals($object) {
        if (!($object instanceof Enum)) {
            return false;
        }
        
        return $this->strict ? ($this->value === $object->value)
            : ($this->value == $object->value);
    }
}