<?php
require_once 'libs/CronJobberPhp/CronJobber.php';
require_once 'libs/CronJobberPhp/Job.php';

class CronJobberTest extends PHPUnit_Framework_TestCase
{
	private $_testJobFileLocation;
	
	private $_testJobFileContents =
		"#this is a comment
		1M ls ~
		5M ls /
		00:00:01 ls /var/www";
	
	private $_testLogFileContents =
		"7fda73e7e28a51b7267158fe9a6d3235 2011-01-05 12:00:00
         9a47901e6558a0a5c02652652c371618 2011-01-05 13:00:00
         c87e0cfa4049501bb33206b780f225bd 2011-01-05 14:00:00";
	
	public function __construct()
	{
		$this->_testJobFileLocation = dirname(__FILE__).'/testJobs';
		$this->_testLogFileLocation = dirname(__FILE__).'/testLog';
		parent::__construct();
	}
	
	public function testLoadJobFile()
	{
		$jobber = new libs_CronJobberPhp_CronJobber();
		
		if( file_exists($this->_testJobFileLocation) ) {
			unlink($this->_testJobFileLocation);
		}
		
		file_put_contents($this->_testJobFileLocation, $this->_testJobFileContents);
		
		$this->assertEquals(
			$jobber->loadJobFile($this->_testJobFileLocation),
			$this->_testJobFileContents
		);
		
		unlink($this->_testJobFileLocation);
	}
	
	public function testLoadLogFile()
	{
		$jobber = new libs_CronJobberPhp_CronJobber();
		
		if( file_exists($this->_testLogFileLocation) ) {
			unlink($this->_testLogFileLocation);
		}
		
		$this->assertFalse(
			$jobber->loadLogFileIfExists($this->_testLogFileLocation)
		);
		
		file_put_contents($this->_testLogFileLocation, $this->_testLogFileContents);
		
		$this->assertEquals(
			$jobber->loadLogFileIfExists($this->_testLogFileLocation),
			$this->_testLogFileContents
		);
	}

	public function testParseLogFile()
	{
		$jobber = new libs_CronJobberPhp_CronJobber();

		$this->assertEquals(
			$jobber->parseLogFile($this->_testLogFileContents),
			array(
				$jobber->hashJobLine('1M ls ~') => strtotime('2011-01-05 12:00:00'),
				$jobber->hashJobLine('5M ls /') => strtotime('2011-01-05 13:00:00'),
				$jobber->hashJobLine('00:00:01 ls /var/www') => strtotime('2011-01-05 14:00:00')
			)
		);
	}
	
	/**
	 * @depends testLoadLogFile
	 * @depends testParseLogFile
	 */
	public function testWriteLogFile()
	{
		$jobber = new libs_CronJobberPhp_CronJobber();
	
		$beforeParsedLog = $jobber->parseLogFile($this->_testLogFileContents);
		
		$jobber->writeLogFile(
			$this->_testLogFileLocation,
			$beforeParsedLog
		);
		
		$this->assertEquals(
			$beforeParsedLog,
			$jobber->parseLogFile(
				$jobber->loadLogFileIfExists($this->_testLogFileLocation)
			)
		);
	}
	
	public function testParseJobFile()
	{
    	$jobber = new libs_CronJobberPhp_CronJobber();
    	
    	$parsedJobs = $jobber->parseJobFile($this->_testJobFileContents);

    	$this->assertEquals(count($parsedJobs),3);
    	
    	$this->assertEquals(
    		$parsedJobs[$jobber->hashJobLine('1M ls ~')],
    		'1M ls ~'
    	);
    	$this->assertEquals(
    		$parsedJobs[$jobber->hashJobLine('5M ls /')],
    		'5M ls /'
    	);
    	$this->assertEquals(
    		$parsedJobs[$jobber->hashJobLine('00:00:01 ls /var/www')],
    		'00:00:01 ls /var/www'
    	);
	}
	
	public function testProcessReplaceVars()
	{
		$jobber = new libs_CronJobberPhp_CronJobber();
		
		$this->assertEquals(
    		'ls /home/sanity',
    		$jobber->processReplaceVarsForCmd(
    			'ls /home/sanity',
    			array()
    		)
    	);
		
		$this->assertEquals(
    		'ls /home/test',
    		$jobber->processReplaceVarsForCmd(
    			'ls {dir}',
    			array('dir' => '/home/test')
    		)
    	);
    	
    	$this->assertEquals(
    		'ls /home/test/project',
    		$jobber->processReplaceVarsForCmd(
    			'ls /home/{user}/{dir}',
    			array(
    				'user' => 'test',
    				'dir' => 'project'
    			)
    		)
    	);
	}
	
	/**
	 * @depends testParseJobFile
	 */
	public function testCreateJobs()
	{
		$jobber = new libs_CronJobberPhp_CronJobber();
		$parsedJobLines = $jobber->parseJobFile($this->_testJobFileContents);
		$parsedJobHashes = array_keys($parsedJobLines);
		$jobs = $jobber->createJobs($parsedJobLines);
		
		$this->assertEquals($jobs[0]->timeStr, '1M');
		$this->assertEquals($jobs[1]->timeStr, '5M');
		$this->assertEquals($jobs[2]->timeStr, '00:00:01');
		$this->assertEquals($jobs[0]->cmd, 'ls ~');
		$this->assertEquals($jobs[1]->cmd, 'ls /');
		$this->assertEquals($jobs[2]->cmd, 'ls /var/www');
		$this->assertEquals($jobs[0]->hash,	$parsedJobHashes[0]);
		$this->assertEquals($jobs[1]->hash,	$parsedJobHashes[1]);
		$this->assertEquals($jobs[2]->hash,	$parsedJobHashes[2]);
	}
    
	public function testEnsureLockDir()
	{
		$jobber = new libs_CronJobberPhp_CronJobber();
		
		$dir = dirname(__FILE__).'/lockTest';
		$jobber->ensureLockDirectoryExists($dir);
		$this->assertTrue(file_exists($dir), 'File was not created');
		rmdir($dir);
	}
	
	/**
	 * @depends testEnsureLockDir
	 * @depends testCreateJobs
	 */
	public function testLocks()
	{
		$jobber = new libs_CronJobberPhp_CronJobber();
		
		$parsedJobLines = $jobber->parseJobFile($this->_testJobFileContents);
		$jobs = $jobber->createJobs($parsedJobLines);
		
		$lockDir = dirname(__FILE__).'/lockTest';
		$jobber->ensureLockDirectoryExists($lockDir);
		
		$this->assertTrue(
			$jobber->getLockForJob($jobs[0], $lockDir),
			'Lock was not grabbed'	
		);
		$this->assertTrue(
			$jobber->getLockForJob($jobs[1], $lockDir),
			'Lock was not grabbed'	
		);
		$this->assertFalse(
			$jobber->getLockForJob($jobs[0], $lockDir),
			'Lock was able to be grabbed when already taken'	
		);
		$this->assertTrue(
			$jobber->releaseLockForJob($jobs[0], $lockDir),
			'Lock was not released'
		);
		$this->assertTrue(
			$jobber->releaseLockForJob($jobs[1], $lockDir),
			'Lock was not released'
		);
		
		rmdir($lockDir);
	}
}