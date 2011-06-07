<?php
require_once 'libs/CronJobberPhp/CronJobber.php';
require_once 'libs/CronJobberPhp/Job.php';

class CronJobTest extends PHPUnit_Framework_TestCase
{
	public function testGetNextRun()
	{
		//constructor data not needed for this test
		$job = new libs_CronJobberPhp_Job('-', '-', '-');

		$this->assertEquals(
			60 * 60 * -1,
			$job->getNextRun(
				'13:00:00', 
				strtotime('2011-01-04 13:00:00'), 
				strtotime('2011-01-05 14:00:00')
			)
		);
		$this->assertEquals(
			60 * 60,
			$job->getNextRun(
				'13:00:00', 
				strtotime('2011-01-04 13:00:00'), 
				strtotime('2011-01-05 12:00:00')
			)
		);
		$this->assertEquals(
			60 * 60 * 2 * -1,
			$job->getNextRun(
				'13:00:00', 
				strtotime('2011-01-04 13:00:00'), 
				strtotime('2011-01-05 15:00:00')
			)
		);
		
		$this->assertEquals(
			0,
			$job->getNextRun(
				'1H', 
				strtotime('2011-01-04 12:00:00'), 
				strtotime('2011-01-04 13:00:00')
			)
		);
		$this->assertEquals(
			-60,
			$job->getNextRun(
				'1H', 
				strtotime('2011-01-04 12:00:00'), 
				strtotime('2011-01-04 13:00:60')
			)
		);
		$this->assertEquals(
			60,
			$job->getNextRun(
				'1H', 
				strtotime('2011-01-04 12:00:60'), 
				strtotime('2011-01-04 13:00:00')
			)
		);
		$this->assertEquals(
			30,
			$job->getNextRun(
				'1M', 
				strtotime('2011-01-04 12:00:00'), 
				strtotime('2011-01-04 12:00:30')
			)
		);
		$this->assertEquals(
			-30,
			$job->getNextRun(
				'1M', 
				strtotime('2011-01-04 12:00:00'), 
				strtotime('2011-01-04 12:01:30')
			)
		);
		$this->assertEquals(
			0,
			$job->getNextRun(
				'1M', 
				strtotime('2011-01-04 12:00:00'), 
				strtotime('2011-01-04 12:01:00')
			)
		);
		$this->assertEquals(
			0,
			$job->getNextRun(
				'5M', 
				strtotime('2011-01-04 12:00:00'), 
				strtotime('2011-01-04 12:05:00')
			)
		);
		$this->assertEquals(
			60*60*23,
			$job->getNextRun(
				'10:00:00', 
				strtotime('2011-01-04 10:30:00'), 
				strtotime('2011-01-04 11:00:00')
			)
		);
		$this->assertEquals(
			60*60*-1,
			$job->getNextRun(
				'10:00:00', 
				strtotime('2011-01-05 11:00:00'), 
				strtotime('2011-01-10 11:00:00')
			)
		);
	}
}