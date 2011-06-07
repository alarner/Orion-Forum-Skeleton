<?php
function webPathToFullPath( $webPath )
{
	return UserConfig::$pathToCode.'/htdocs'.$webPath;
}