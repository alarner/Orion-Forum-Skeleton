<?php
class CollectionVO extends ArrayObject
{
	function CollectionVO($config = array())
	{
 		parent::__construct(array(), ArrayObject::ARRAY_AS_PROPS);
	}

    protected function _getRowByValue( $searchKey, $searchValue )
    {
    	if( count($this) == 0 ) return FALSE;
    	
    	foreach( $this as $value ) {
    		if( $value->$searchKey == $searchValue ) {
    			return $value;
    		}
    	}
    	
    	return FALSE;
    }
    
    public function drawList( $displayParam, $sep = ', ' )
    {
		$output = '';
    	$first = true;
		foreach( $this as $item ) {
			if( $first ) $first = false;
			else         $output .= $sep;
			
			$output .= $item->$displayParam;
		}
		return $output;
    }

	private $_sortingBy;
	private $_sortType; //string, numeric

	public function sortBy( $index , $sortType = 'string' )
	{
		$this->_sortingBy = explode('.',$index);
		$this->_sortType = $sortType;
		$this->uasort(array($this, '_sortByHelper'));
	}
	public function _sortByHelper( $a, $b )
	{
		$aVal=$a; $bVal=$b;
		foreach( $this->_sortingBy as $sortByProp )
		{
			$aVal = $aVal->$sortByProp;
			$bVal = $bVal->$sortByProp;
		}

		if( $this->_sortType == 'numeric' ) {
			if( $aVal > $bVal ) return -1;
 			elseif( $bVal > $aVal ) return 1;
			return 0;
		}
		elseif( $this->_sortType == 'string' ) {
			return strcmp($aVal, $bVal);
		}
		
		throw new Exception('Invalid comparison type '.$this->_sortType);
	}
}
