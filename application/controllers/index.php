<?php
require_once('root.php');

class Index extends Root {

	function __construct() {
		parent::__construct();
	}

	public function index() {
		$this->load->model('Company_Model');
		
		$params = array();
		$params['companies'] = $this->Company_Model->getCompanies();
		$this->load->view('index', $params);
	}
}
