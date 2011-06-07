<?php
class libs_CronJobberPhp_CronJobber
{
	const JOB_FILE_NAME = 'jobs';
	const LOG_FILE_NAME = '.log';
	const LOCK_DIR_NAME = '.lock';

	public $logFileLocation;
	public $lockFileDir;
	
	private $_cliParams;
	
	public function __construct( $cliParams = array() )
	{
		$this->_cliParams = $cliParams;
		$this->jobFile = dirname(__FILE__).'/'.self::JOB_FILE_NAME;
		$this->logFileLocation = dirname(__FILE__).'/'.self::LOG_FILE_NAME;
		$this->lockFileDir = dirname(__FILE__).'/'.self::LOCK_DIR_NAME;
	}
	
	public function run()
	{
		$jobs = 
			$this->createJobs(
				$this->parseJobFile(
					$this->loadJobFile($this->jobFile)
				),
				$this->_cliParams
			);

		$logs = 
			$this->parseLogFile(
				$this->loadLogFileIfExists($this->logFileLocation)
			);	
			
		$this->ensureLockDirectoryExists($this->lockFileDir);	
		
		foreach( $jobs as $job ) {
			if( !isset($logs[$job->hash])
			    || $job->shouldRunNow($logs[$job->hash]) )
			{
				if( $this->getLockForJob($job, $this->lockFileDir) ) {
					$job->runAsync();
					$logs[$job->hash] = time();
				}
			}
		}
		
		$this->writeLogFile($this->logFileLocation, $logs);
	}
	
	public function loadJobFile( $file )
	{
		if( !file_exists($file) ) {
			throw new Exception('Unable to load job file: "'.$file.'"');
		}
		return file_get_contents($file);
	}
	
	public function loadLogFileIfExists( $file )
	{
		if( !file_exists($file) ) {
			return FALSE;
		}
		return file_get_contents($file);
	}
	
	public function writeLogFile( $fileLocation, $logHashes )
	{
		$fileContents = '';
		foreach( $logHashes as $hash => $time ) {
			$fileContents .= $hash.' '.date('Y-m-d H:i:s', $time)."\n";
		}
		file_put_contents($fileLocation, $fileContents);
	}
	
	public function parseJobFile( $fileContents )
	{
		$jobsHash = array();
		foreach( explode("\n",$fileContents) as $jobLine ) {
			$trimmedJobLine = trim($jobLine);
			if( $trimmedJobLine == FALSE || $trimmedJobLine[0] == '#' ) { 
				continue;	
			}
			$newJobHash = $this->hashJobLine($trimmedJobLine);
			if( isset($jobsHash[$newJobHash]) ) {
				throw new Exception('Duplicate job: "'.$trimmedJobLine.'"');
			}
			$jobsHash[$newJobHash] = $trimmedJobLine;
		}
		return $jobsHash;
	}
	
	public function parseLogFile( $logFileContents )
	{
		$logHashes = array();
		foreach( explode("\n",$logFileContents) as $logLine ) {
			$trimmedLogLine = trim($logLine);
			if( $trimmedLogLine == FALSE ) { 
				continue;
			}
			$logBits = explode(' ', $trimmedLogLine);
			if( count($logBits) < 3 ) {
				throw new Exception('Invalid Log line "'.$trimmedLogLine.'"');
			}
			$logHash = $logBits[0];
			$logTime = $logBits[1].' '.$logBits[2];
			if( isset($logHashes[$logHash]) ) {
				throw new Exception('Duplicate log: "'.$logHash.'"');
			}
			$logHashes[$logHash] = strtotime($logTime);
		}
		return $logHashes;
	}
	
	public function createJobs( $jobsHash, $replaceVars = array() )
	{
		$jobs = array();
		foreach( $jobsHash as $hash => $job ) { 
			$firstSpaceLocation = strpos($job, ' ');
			$timeStr = substr($job, 0, $firstSpaceLocation);
			$cmd = trim(substr($job, $firstSpaceLocation));
			if( !empty($replaceVars) ) {
				$cmd = $this->processReplaceVarsForCmd($cmd, $replaceVars);
			}
			$jobs[] = new libs_CronJobberPhp_Job($timeStr, $cmd, $hash);
		}
		return $jobs;
	}
	
	public function processReplaceVarsForCmd( $cmd, $replaceVars )
	{
		$search = array();
		$replace = array();
		foreach( $replaceVars as $findKey => $replaceVal ) {
			$search[] = '{'.$findKey.'}';
			$replace[] = $replaceVal;
		}
		return str_replace($search, $replace, $cmd);
	}
	
	public function ensureLockDirectoryExists( $lockDir )
	{
		if( !file_exists($lockDir) ) {
			mkdir($lockDir);
		}
	}
	
	public function getLockForJob( libs_CronJobberPhp_Job $job, $lockDir )
	{
		$lockFile = $lockDir.'/'.$job->hash;
		if( file_exists($lockFile) ) {
			return FALSE;
		}
		touch($lockFile);
		return TRUE;
	}
	
	public function releaseLockForJob( libs_CronJobberPhp_Job $job, $lockDir )
	{
		$lockFile = $lockDir.'/'.$job->hash;
		if( !file_exists($lockFile) ) {
			return FALSE;
		}
		unlink($lockFile);
		return TRUE;
	}
	
	public function hashJobLine( $line )
	{
		return md5($line);
	}
}