<?php

class Root extends CI_Controller {

	protected $errors;
	protected $previousPost;
	protected $user;

	function __construct() {
		parent::__construct();
		session_start();
		$this->load->driver('cache', array('adapter' => UserConfig::$cacheAdapter, 'backup' => 'file'));
		$this->errors = array();
		$this->previousPost = array();
		$this->user = array();
		
		if(array_key_exists('ERRORS', $_SESSION)) {
			$this->errors = $_SESSION['ERRORS'];
		}
		unset($_SESSION['ERRORS']);
		
		if(array_key_exists('POST', $_SESSION)) {
			$this->previousPost = $_SESSION['POST'];
		}
		unset($_SESSION['POST']);
		
		if(array_key_exists('USER', $_SESSION)) {
			$this->user = $_SESSION['USER'];
		}
	}
	
	protected function addError($message, $key = 'default') {
		if(!array_key_exists($key, $this->errors)) {
			$this->errors[$key] = array();
		}
		$this->errors[$key][] = $message;
	}
	
	protected function hasErrors() {
		return count($this->errors) > 0;
	}
	
	protected function storePost() {
		$_SESSION['POST'] = $_POST;
	}
	
	protected function storeErrors() {
		$_SESSION['ERRORS'] = $this->errors;
	}
	
	protected function isLoggedIn() {
		return (count($this->user) > 0);
	}
}
