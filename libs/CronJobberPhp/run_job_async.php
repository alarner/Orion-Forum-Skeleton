<?php
//No time limit and a bunch of memory
//Change these settings if you'd like a bit stricter rules
set_time_limit(0);
ini_set('memory_limit', '256M');

require_once dirname(__FILE__).'/CronJobber.php';
require_once dirname(__FILE__).'/Job.php';

$jobToRun = new libs_CronJobberPhp_Job(
	$argv[1],
	$argv[2],
	$argv[3]
);

print_r($argv);
$jobToRun->exec();

$jobber = new libs_CronJobberPhp_CronJobber();
$jobber->releaseLockForJob($jobToRun, $jobber->lockFileDir);