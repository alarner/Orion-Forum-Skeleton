<?php
require_once dirname(__FILE__).'/CronJobber.php';
require_once dirname(__FILE__).'/Job.php';

array_shift($argv);
$params = array();
foreach( $argv as $argument )
{
	$keyValue = explode('=',$argument);
	$params[$keyValue[0]] = $keyValue[1];
}

$jobber = new libs_CronJobberPhp_CronJobber($params);

$jobber->run();