<?php
class User_Model extends CI_Model {

	function __construct() {
		parent::__construct();
		$this->load->database();
	}
	
	public function register($companyId, $email, $password) {
		// First check if an account already exists
		$sql = '
		SELECT COUNT(*) AS `num` 
		FROM `users` 
		WHERE `email` = ? AND `company_id` = ?
		LIMIT 1';
		
		$encryptedEmail = $this->encryptEmail(
			$email, 
			UserConfig::$emailKey, 
			UserConfig::$emailIv
		);
		
		$params = array(
			$encryptedEmail,
			$companyId
		);
		
		$dupes = $this->db->query($sql, $params)->row_array();
		
		if($dupes['num'] == 0) {
			$sql = '
			INSERT INTO `users` 
			(`email`, `password`, `company_id`, `salt`, `created`)
			VALUES(?,?,?,?,NOW())';
			
			$userSalt = $this->generateSalt();
			
			$params = array(
				$encryptedEmail,
				$this->hashPassword(
					$password, 
					$userSalt, 
					UserConfig::$passwordGlobalSalt
				),
				$companyId,
				$userSalt
			);
			
			$this->db->query($sql, $params);
			return true;
		}
		return false;
	}
	
	public function authenticate($companyId, $email, $password) {
		$sql = '
		SELECT 
			`user_id`, 
			`email`, 
			`password`, 
			`company_id`, 
			`salt`, 
			`active`, 
			`created`, 
			`last_login` 
		FROM `users`
		WHERE 
			`company_id` = ? AND
			`email` = ?
		LIMIT 1';
				
		$params = array(
			$companyId,
			$this->encryptEmail(
				$email, 
				UserConfig::$emailKey, 
				UserConfig::$emailIv
			)
		);
		
		$user = $this->db->query($sql, $params)->row_array();
		if(count($user) > 0) {
			$hashedPassword = $this->hashPassword(
				$password, 
				$user['salt'], 
				UserConfig::$passwordGlobalSalt
			);
			
			if($user['password'] == $hashedPassword) {
				return $user;
			}
		}
		return false;
	}
	
	private function encryptEmail($unencrypted, $key, $iv) {
		$encrypted = mcrypt_encrypt(
			MCRYPT_RIJNDAEL_256, 
			$key, 
			$unencrypted, 
			MCRYPT_MODE_CFB, 
			base64_decode($iv)
		);
		
		return base64_encode($encrypted);
	}
	
	private function decryptEmail($encrypted, $key, $iv) {
		$encrypted = base64_decode($encrypted);
		$decrypted = mcrypt_decrypt(
			MCRYPT_RIJNDAEL_256,
			$key,
			$encrypted, 
			MCRYPT_MODE_CFB, 
			base64_decode($iv)
		);
		
		return $decrypted;
	}
	
	private function hashPassword($password, $userSalt, $globalSalt) {
		return hash_hmac('sha256', $password, $userSalt . $globalSalt);
	}
	
	private function generateSalt() {
		$salt = '';
		for($i=0; $i<24; $i++) {
			$asciiCode = rand(32, 126); // Printable ascii characters
			$salt .= chr($asciiCode);
		}
		return $salt;
	}
}
