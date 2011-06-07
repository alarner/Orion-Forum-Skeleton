<?php
class MySQLHelper
{
	private $_db; //CI's db library

	public function __construct( &$db )
	{
		$this->_db =& $db;
	}

	public function execute( $sql /*, $params = array() */ )
	{
		$sql = trim($sql);
		$result = $this->_db->query($sql);
		
		if( !$result ) return FALSE;

		if( strtoupper(substr($sql, 0, 6)) == 'SELECT') {
			return $result->result_array();
		}
		return $this->_db->affected_rows();
	}

	protected function _quote( $val )
	{
		if( is_array($val) ) {
			return implode(', ', array_walk($val, array($this, '_quote')) );
		}
		return $this->_db->escape($val);
	}

	public function insert( $table, $rows, $replaceInto=False )
	{
		$insertString = 'INSERT';
		if( $replaceInto ) {
			$insertString = 'REPLACE';
		}
		
		$insertSql = $insertString . ' INTO `' . $table . '` (';
		$insertCols = array();		

		foreach( $rows[0] as $key => $value ) {
			$insertCols[] = $key;
		}

		$insertSql .= implode(', ',$insertCols) . ') VALUES ';

		$rowsToInsert = array();
		foreach( $rows as $row ) {
			$rowToInsert = '';
			foreach( $insertCols as $col ) {			
				$rowToInsert[]  = $this->_quote($row[$col]);
			}
			$rowsToInsert[] = implode(', ',$rowToInsert);
		}

		$insertSql .= '(' . implode('),(', $rowsToInsert) . ')';

		return $this->execute($insertSql);
	}
}
