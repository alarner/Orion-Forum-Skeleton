<?php
class ReflectPublicPropertyIterator implements Iterator
{
	private $myArray;

	public function __construct( $properties )
	{
		foreach( $properties as $key=>&$property )
		{					
			if( $property->isStatic() )
			{
				unset($properties[$key]);
				continue;	
			}
			
			//Calling getProperties(ReflectionProperty::IS_PUBLIC) also happens to return
			//protected properties. To deal with this we filter out variables that start with '_'
			if( substr($property->getName(), 0, 1) == '_' )
			{
				unset($properties[$key]);
			}
		}
		
		$this->myArray = $properties;
	}
  
	function rewind()
	{
		return reset($this->myArray);
	}
  
	function current()
	{
		return current($this->myArray);
	}
  
	function key()
	{
		return key($this->myArray);
	}
  
	function next()
	{
		return next($this->myArray);
	}
  
	function valid()
	{
		return key($this->myArray) !== null;
	}
}