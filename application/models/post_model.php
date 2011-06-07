<?php
class Post_Model extends CI_Model {

	const CACHE_PREFIX = 'company_model::';

	function __construct() {
		parent::__construct();
		$this->load->database();
	}
	
	public function createPost($topic, $body, $companyId, $ipHash, $parentPostId = null, $userId = null) {
		// First a sanity check to avoid double posting
		$sql = '
		SELECT COUNT(*) AS `num` FROM `posts` 
		WHERE 
			`body` = ? AND
			`ip` = ? AND 
			`company_id` = ? AND 
			`parent_post_id` ';
		$sql .= ($parentPostId === null) ? 'IS' : '=';
		$sql .= ' ? AND
			`created` > DATE_SUB(NOW(), INTERVAL 15 SECOND) 
		LIMIT 1';
		
		$params = array(
			$body,
			$ipHash,
			$companyId,
			$parentPostId
		);
		$reposts = $this->db->query($sql, $params)->row_array();
				
		if($reposts['num'] == 0) {
			$rootPostId = null;
			if($parentPostId !== null) {
				$parentPost = $this->getPost($parentPostId);
				$rootPostId = $parentPost['root_post_id'];
				if($rootPostId === null) {
					$rootPostId = $parentPostId;
					$parentPostId = null;
				}
			}
			
			$this->db->query('START TRANSACTION');
			
			$sql = '
			INSERT INTO `posts` (
				`company_id`, 
				`user_id`, 
				`topic`, 
				`body`, 
				`parent_post_id`, 
				`root_post_id`, 
				`created`, 
				`last_reply`, 
				`ip`)
			VALUES(?,?,?,?,?,?,NOW(),NULL,?)';
			$this->db->query(
				$sql,
				array(
					$companyId,
					$userId,
					$topic,
					$body,
					$parentPostId,
					$rootPostId,
					$ipHash
				)
			);
			
			if($rootPostId !== null) {
				$sql = '
				UPDATE `posts` 
				SET `last_reply` = NOW(), `comments` = `comments` + 1
				WHERE `post_id` = ? OR `post_id` = ?';
				$this->db->query($sql, array($parentPostId, $rootPostId));
			}
			
			$this->db->query('COMMIT');
			return true;
		}
		return false;
	}
	
	public function getPostsFromCompanyId(
		$companyId,
		$page,
		$max,
		$order = CC::ORDER_REPLY_DATE,
		$rootPostId = null) {

		$sql = '
		SELECT
			`post_id`,
			`company_id`,
			`user_id`,
			`topic`,
			`body`,
			`parent_post_id`,
			`root_post_id`,
			`created`,
			`last_reply`,
			`votes`
		FROM `posts`
		WHERE 
			`company_id` = ? AND
			`root_post_id` ';
		$sql .= ($rootPostId === null) ? 'IS' : '=';
		$sql .= ' ? AND `hidden` = 0
		ORDER BY ' . $this->orderCodeToSql($order) . '
		LIMIT ' . intval(($page-1)*$max) . ', ' . intval($max);
		
		
		return $this->db->query(
			$sql,
			array($companyId, $rootPostId)
		)->result_array();
	}
	
	public function getPost($postId) {
		// First get topic
		$sql = '
		SELECT
			`post_id`,
			`company_id`,
			`user_id`,
			`topic`,
			`body`,
			`parent_post_id`,
			`root_post_id`,
			`created`,
			`last_reply`,
			`votes`
		FROM `posts`
		WHERE `post_id` = ? AND `hidden` = 0 LIMIT 1';
		
		return $this->db->query($sql, array($postId))->row_array();
	}
	
	public function getRootPostReplies($rootPostId, $page, $max) {
		// First get topic
		$sql = '
		SELECT
			p1.`post_id`,
			p1.`company_id`,
			p1.`user_id`,
			p1.`body`,
			p1.`parent_post_id`,
			p1.`root_post_id`,
			p1.`created`,
			p1.`last_reply`,
			p1.`votes`,
			p2.`body` AS `parent_body`
		FROM `posts` p1
		LEFT JOIN `posts` p2 ON (p2.`post_id` = p1.`parent_post_id` AND p2.`hidden` = 0)
		WHERE 
			p1.`root_post_id` = ? AND 
			p1.`hidden` = 0 
		ORDER BY `created` ASC
		LIMIT ' . intval(($page-1)*$max) . ', ' . intval($max);
		
		return $this->db->query($sql, array($rootPostId))->result_array();
	}
	
	private function orderCodeToSql($orderCode) {
		switch($orderCode) {
			case CC::ORDER_CREATED_DATE:
				return '`created` DESC';
			case CC::ORDER_REPLY_DATE:
				return '`last_reply` DESC';
			case CC::ORDER_NUM_VOTES:
				return '`votes` DESC';
			case CC::ORDER_NUM_REPORTS:
				return '`reports` DESC';
			case CC::ORDER_NUM_COMMENTS:
				return '`comments` DESC';
		}
		throw new Exception('Unknown error code encountered in Post_Model::orderCodeToSql');
	}
}
