<?php
function mysql_now()
{
	return date('Y-m-d H:i:s');
}

function mysql_from_unix( $unixTime )
{
	return date('Y-m-d H:i:s', $unixTime );
}
