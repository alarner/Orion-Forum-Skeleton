<?php
class libs_CronJobberPhp_Job
{
	const SECONDS_MOD_H = '* 60 * 60';
	const SECONDS_MOD_M = '* 60';
	
	public $timeStr;
	public $cmd;
	public $hash;
	
	public function __construct( $timeStr, $cmd, $hash )
	{
		$this->timeStr = $timeStr;
		$this->cmd = $cmd;
		$this->hash = $hash;
	}
	
	public function runAsync()
	{
		$asyncCmd =
			'php '.dirname(__FILE__).'/run_job_async.php '.
			$this->timeStr.' "'.
			$this->cmd.'" '.
			$this->hash .' &> /dev/null &';
		
		echo $asyncCmd."\n";	
		exec($asyncCmd);
	}
	
	public function exec()
	{
		exec($this->cmd);
	}
	
	public function shouldRunNow( $lastRun )
	{
		echo 'getNextRun('.$this->timeStr.', '.$lastRun.') = '.
			$this->getNextRun($this->timeStr, $lastRun)."\n";
		if( $this->getNextRun($this->timeStr, $lastRun) <= 0 ) {
			return TRUE;
		}
		return FALSE;
	}
	
	public function getNextRun( $timeStr, $lastRun, $currentTime = null )
	{
		if( $currentTime === null ) {
			$currentTime = time();
		}
			
		if( $lastRun > $currentTime ) {
			throw new Exception('Command cannot have been run in the future.');
		}
		
		if( preg_match('/[0-9]{2}:[0-9]{2}:[0-9]{2}/', $timeStr) ) {
			$todayRun = strtotime(date('Y-m-d', $currentTime).' '.$timeStr);
			$tomorrowRun = strtotime(date('Y-m-d', $currentTime+60*60*24).' '.$timeStr);
			if( $currentTime > $todayRun && $todayRun > $lastRun ) {
				return $todayRun - $currentTime;
			}
			if( $currentTime < $todayRun ) {
				return $todayRun - $currentTime;
			}
			return $tomorrowRun - $currentTime;
		}
		else if( preg_match('/[0-9]+[M|H|m|h]/', $timeStr) ) {
			$number = (int)substr($timeStr, 0, strlen($timeStr)-1);
			$letter = strtolower(substr($timeStr, -1));
			
			//Turn H or M into a multiplication statement and eval it
			$letterMod = self::SECONDS_MOD_M;
			if( $letter == 'h' ) {
				$letterMod = self::SECONDS_MOD_H;
			}
			eval('$secondsMod = '.$number.$letterMod.';');
			return ($currentTime - $lastRun - $secondsMod) * -1;
		}
		else {
			throw new Exception("Unknown time format '".$timeStr."'");	
		}
	}
}