<?php
class VO
{
	public function merge( $anotherVO )
	{
		$reflect = new ReflectionObject($this);
		
		$thisClass = get_class();
		$resultVO = new $thisClass();

		$propertiesIt = new ReflectPublicPropertyIterator(
			$reflect->getProperties(ReflectionProperty::IS_PUBLIC)
		);

		foreach( $propertiesIt as $property )
		{
			$propertyName = $property->getName();

			if( is_object($this->$propertyName)
				&& is_subclass_of($this->$propertyName, 'VO') )
			{
				$this->$propertyName->merge($anotherVO->$propertyName);
			}
			else
			{
				$this->$propertyName = $anotherVO->$propertyName;
			}
		}
	}
}