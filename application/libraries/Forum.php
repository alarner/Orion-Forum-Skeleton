<?php
class Forum {
	const MAX_POSTS_PER_PAGE = 25;
	const MAX_REPLIES_PER_PAGE = 25;
	
	const ORDER_CREATED_DATE 	= 0;
	const ORDER_REPLY_DATE 		= 1;
	const ORDER_NUM_VOTES 		= 2;
	const ORDER_NUM_REPORTS		= 3;
	const ORDER_NUM_COMMENTS 	= 4;
	
	public function hashIp($ip, $salt) {
		return hash_hmac('sha256', $ip, $salt);
	}
}
