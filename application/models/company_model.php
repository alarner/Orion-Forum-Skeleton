<?php
class Company_Model extends CI_Model {

	const CACHE_PREFIX = 'company_model::';

	function __construct() {
		parent::__construct();
		$this->load->database();
	}
	
	public function getCompanies() {
		$sql = '
		SELECT `company_id`, `name`, `slug`, `url` 
		FROM `companies`
		ORDER BY `name` ASC';
		return $this->db->query($sql)->result_array();
	}
	
	public function getCompanyFromSlug($slug) {
		// Look for the company info in the cache
		$cacheKey = Company_Model::CACHE_PREFIX . 'getCompanyFromSlug::' . $slug;
		$result = $this->cache->get($cacheKey);
		
		// Grab from the db if the value doesn't exist in the cache
		if($result === false) {
			$sql = '
			SELECT `company_id`, `name`, `slug`, `url`, `twitter_user`
			FROM `companies`
			WHERE `slug` = ?
			LIMIT 1';
			
			$result = $this->db->query($sql, array($slug))->row_array();
			$this->cache->save($cacheKey, $result);
		}
		
		return $result;
	}
}
