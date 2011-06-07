<?php
require_once('root.php');

class Company extends Root {
	
	const ERROR_MISSING_TOPIC = 'A topic is required.';
	const ERROR_MISSING_BODY = 'A body is required.';
	const ERROR_CAPTCHA = 'Please fill in the box below to pass the spam filter.';
	const ERROR_QUICK_POST = 'You are posting too quickly. Please wait before making another post.';
	const ERROR_MISSING_EMAIL = 'An email is required.';
	const ERROR_INVALID_EMAIL = 'This email address is invalid.';
	const ERROR_EMAIL_REGISTERED = 'This email address has already been registered.';
	const ERROR_MISSING_PASSWORD = 'A password is required.';
	const ERROR_MISSING_PASSWORD_CONFIRMATION = 'A password confirmation is required.';
	const ERROR_PASSWORD_MATCH = 'Passwords must match.';
	const ERROR_AUTHENTICATE = 'Could not find the specified email/password combination.';
	const ERROR_USER_INACTIVE = 'The specified account is not active. Please contact us for details.';

	private $params;
	private $company;

	function __construct() {
		parent::__construct();
	}

	public function index($page = 1, $order = CC::ORDER_REPLY_DATE) {
		$this->load->model('Post_Model');
		$posts = $this->Post_Model->getPostsFromCompanyId(
			$this->company['company_id'],
			$page,
			CC::MAX_POSTS_PER_PAGE,
			$order,
			null
		);
		$this->orion->ezdie($posts);
		$this->params->header['title'] = 'Corporate Confidential - ' . $this->company['name'] . ' board';
		$this->render('company/index');
	}
	
	public function topic($rootPostId, $page = 1) {
		$this->load->model('Post_Model');
		$rootPost = $this->Post_Model->getPost($rootPostId);
		
		$replies = $this->Post_Model->getRootPostReplies(
			$rootPostId, 
			$page, 
			CC::MAX_REPLIES_PER_PAGE
		);
		
		if(count($rootPost) == 0 || ($page > 1 && count($replies) == 0)) {
			show_error('Could not find page.');
		}
		
		$this->render('company/topic', array('rootPost'=>$rootPost, 'replies'=>$replies));
	}
	
	public function postForm() {
		$this->load->library('recaptchalib');
		$params = array(
			'errors' => $this->errors,
			'post' => $this->previousPost,
			'slug' => $this->company['slug'],
			'captcha' => $this->recaptchalib->recaptcha_get_html(UserConfig::$recaptchaPublic)
		);
		$this->render('company/postForm', $params);
	}
	
	public function post() {
		$this->load->library('recaptchalib');
		$topic = $this->input->post('topic');
		$body = $this->input->post('body');
		if($topic === false || strlen($topic) == 0) {
			$this->addError(Company::ERROR_MISSING_TOPIC, 'topic');
		}
		if($body === false || strlen($body) == 0) {
			$this->addError(Company::ERROR_MISSING_BODY, 'body');
		}
		
		$captchaResult = $this->recaptchalib->recaptcha_check_answer (
			UserConfig::$recaptchaPrivate,
			$_SERVER['REMOTE_ADDR'],
			$this->input->post('recaptcha_challenge_field'),
			$this->input->post('recaptcha_response_field')
		);
		
		if(!$captchaResult->is_valid) {
			$this->addError(Company::ERROR_CAPTCHA, 'captcha');
		}

		if(!$this->hasErrors()) {
			$this->load->model('Post_Model');
			$userId = $this->orion->def('user_id', $this->user);
			$postResult = $this->Post_Model->createPost(
				$topic,
				$body,
				$this->company['company_id'],
				$this->forum->hashIp($this->input->ip_address(), UserConfig::$ipHashSalt),
				null,
				$userId
			);
			
			if(!$postResult) {
				$this->addError(Company::ERROR_QUICK_POST);
			}
		}
		
		if($this->hasErrors()) {
			$this->storePost();
			$this->storeErrors();
			redirect('/'.$this->company['slug'].'/postForm');
		}
		echo 'Success!';
	}
	
	public function replyForm($parentPostId) {
		$this->load->model('Post_Model');
		$this->load->library('recaptchalib');
		$parentPost = $this->Post_Model->getPost($parentPostId);
		
		// Ensure that the post we are requesting exists and belongs to
		// the correct company
		if(count($parentPost) == 0 || $parentPost['company_id'] != $this->company['company_id']) {
			show_error('The post to which you are replying could not be found.');
		}
		
		$params = array(
			'errors' => $this->errors,
			'post' => $this->previousPost,
			'slug'=>$this->company['slug'],
			'parentPost'=>$parentPost,
			'captcha' => $this->recaptchalib->recaptcha_get_html(UserConfig::$recaptchaPublic)
		);
		
		$this->render('company/replyForm', $params);
	}
	
	public function reply() {
		$this->load->model('Post_Model');
		$this->load->library('recaptchalib');
		$parentPostId = $this->input->post('parentPostId');
		if($parentPostId === false) {
			show_error('Missing parent post.');
		}
		$parentPost = $this->Post_Model->getPost($parentPostId);
		
		// Ensure that the post we are requesting exists and belongs to
		// the correct company
		if(count($parentPost) == 0 || $parentPost['company_id'] != $this->company['company_id']) {
			show_error('The post to which you are replying could not be found.');
		}
		
		$body = $this->input->post('body');
		if($body === false || strlen($body) == 0) {
			$this->addError(Company::ERROR_MISSING_BODY, 'body');
		}
		
		$captchaResult = $this->recaptchalib->recaptcha_check_answer (
			UserConfig::$recaptchaPrivate,
			$_SERVER['REMOTE_ADDR'],
			$this->input->post('recaptcha_challenge_field'),
			$this->input->post('recaptcha_response_field')
		);
		
		if(!$captchaResult->is_valid) {
			$this->addError(Company::ERROR_CAPTCHA, 'captcha');
		}
		
		if(!$this->hasErrors()) {
			$userId = $this->orion->def('user_id', $this->user);
			$postResult = $this->Post_Model->createPost(
				null,
				$body,
				$this->company['company_id'],
				$this->forum->hashIp($this->input->ip_address(), UserConfig::$ipHashSalt),
				$parentPost['post_id'],
				$userId
			);
			if(!$postResult) {
				$this->addError(Company::ERROR_QUICK_POST);
			}
		}
		
		if($this->hasErrors()) {
			$this->storePost();
			$this->storeErrors();
			redirect('/'.$this->company['slug'].'/replyForm/'.$parentPost['post_id']);
		}
		echo 'Success!';
	}
	
	public function registerForm() {
		$this->load->library('recaptchalib');
		$params = array(
			'errors' => $this->errors,
			'post' => $this->previousPost,
			'slug'=>$this->company['slug'],
			'captcha' => $this->recaptchalib->recaptcha_get_html(UserConfig::$recaptchaPublic)
		);
		$this->render('company/registerForm', $params);
	}
	
	public function register() {
		$this->load->library('recaptchalib');
		
		$email = $this->input->post('email');
		if($email === false || strlen($email) == 0) {
			$this->addError(Company::ERROR_MISSING_EMAIL, 'email');
		}
		
		$password = $this->input->post('password');
		if($password === false || strlen($password) == 0) {
			$this->addError(Company::ERROR_MISSING_PASSWORD, 'password');
		}
		
		$passwordConfirmation = $this->input->post('passwordConfirmation');
		if($passwordConfirmation === false || strlen($passwordConfirmation) == 0) {
			$this->addError(Company::ERROR_MISSING_PASSWORD_CONFIRMATION, 'passwordConfirmation');
		}
		
		$captchaResult = $this->recaptchalib->recaptcha_check_answer (
			UserConfig::$recaptchaPrivate,
			$_SERVER['REMOTE_ADDR'],
			$this->input->post('recaptcha_challenge_field'),
			$this->input->post('recaptcha_response_field')
		);
		
		if(!$captchaResult->is_valid) {
			$this->addError(Company::ERROR_CAPTCHA, 'captcha');
		}
		
		if(strlen($password) > 0 && strlen($passwordConfirmation) > 0 && $password != $passwordConfirmation) {
			$this->addError(Company::ERROR_PASSWORD_MATCH, 'password');
		}
		
		if(strlen($email) > 0) {
			$email = filter_var($email, FILTER_VALIDATE_EMAIL);
			if($email === false) {
				$this->addError(Company::ERROR_INVALID_EMAIL, 'email');
			}
		}
		
		if(!$this->hasErrors()) {
			$this->load->model('User_Model');
			$success = $this->User_Model->register(
				$this->company['company_id'], 
				$email, 
				$password
			);
			
			if(!$success) {
				$this->addError(Company::ERROR_EMAIL_REGISTERED, 'email');
			}
		}
		
		if($this->hasErrors()) {
			$this->storePost();
			$this->storeErrors();
			redirect('/'.$this->company['slug'].'/registerForm');
		}
		echo 'Success!';
	}
	
	public function loginForm() {
		$params = array(
			'errors' => $this->errors,
			'post' => $this->previousPost,
			'slug'=>$this->company['slug'],
		);
		$this->render('company/loginForm', $params);
	}
	
	public function login() {		
		$email = $this->input->post('email');
		if($email === false || strlen($email) == 0) {
			$this->addError(Company::ERROR_MISSING_EMAIL);
		}
		
		$password = $this->input->post('password');
		if($password === false || strlen($password) == 0) {
			$this->addError(Company::ERROR_MISSING_PASSWORD);
		}
		
		if(!$this->hasErrors()) {
			$this->load->model('User_Model');
			$user = $this->User_Model->authenticate(
				$this->company['company_id'], 
				$email, 
				$password
			);
			
			if($user === false) {
				$this->addError(Company::ERROR_AUTHENTICATE);
			}
			else if(!$user['active']) {
				$this->addError(Company::ERROR_USER_INACTIVE);
			}
		}
		
		if($this->hasErrors()) {
			$this->storePost();
			$this->storeErrors();
			redirect('/'.$this->company['slug'].'/loginForm');
		}
		
		$_SESSION['USER'] = $user;
		echo 'Success!';
	}
	
	private function before($method, &$args) {
		// Grab the company slug
		$slug = array_shift($args);
		
		// Get company specific data
		$this->company = $this->Company_Model->getCompanyFromSlug($slug);
		
		// If company doesn't exist then show 404
		if(count($this->company) == 0) {
			show_error($slug . ' is not a company in our database.');
		}
	}
	
	private function after($method, &$args) {
		
	}
	
	private function render($view, $params = array()) {
		if($this->hasErrors()) $params['errors'] = $this->errors;
		$this->load->view('company/header', $this->params->header);
		$this->load->view($view, $params);
		$this->load->view('company/footer', $this->params->footer);
	}
	
	/**
	 * Utilizing the CodeIgniter's _remap function
	 * to call extra functions with the controller action
	 * @see http://codeigniter.com/user_guide/general/controllers.html#remapping
	**/
	public function _remap($method, $args) {
		// Ensure that method exists, otherwise show 404
		if(!method_exists($this, $method)) {
			show_error('Page not found.');
		}
		
		$this->load->model('Company_Model');
		
		$this->params = new stdClass();
		$this->params->header = array(
			'title'=>''
		);
		$this->params->footer = array();
		$this->company = array();
		
		// Call before action
		$this->before($method, $args);

		call_user_func_array(array($this, $method), $args);

		// Call after action
		$this->after($method, $args);
	}
}
