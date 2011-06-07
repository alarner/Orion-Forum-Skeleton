<?php
if( !defined('CONNECT_DB_BASE') )
{
	//path to ConnectForm library from base of php include path
	define('CONNECT_DB_BASE', 'libs/ConnectDb/');
	
	require CONNECT_DB_BASE.'util/ReflectPublicPropertyIterator.php';
	require CONNECT_DB_BASE.'vo/VO.php';
	require CONNECT_DB_BASE.'vo/CollectionVO.php';	

	function connectDbAutoloader( $classname )
	{
		if( strpos($classname, 'CollectionVO') !== FALSE )
		{
			require APPPATH.'vo/collection/'.$classname.'.php';
			return;
		}
		
		if( strpos($classname, 'VO') !== FALSE )
		{
			require APPPATH.'vo/'.$classname.'.php';
			return;
		}
	}
	
	spl_autoload_register('connectDbAutoloader');
}