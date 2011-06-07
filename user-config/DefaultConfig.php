<?php
class DefaultConfig
{
	public static $dbname = 'forum';
	public static $dbuser = 'root';
	public static $dbpass = '';
	public static $dbhost = 'localhost';

	//Path to folder that contains the bootstrap.php file
	public static $pathToCode = '';

	public static $errorLevel = E_ALL;
	
	public static $debugMode = false;
	public static $cacheAdapter = 'apc';
	
	public static $ipHashSalt = 'f24309fjeo3984hf4jf39048hf';
	
	// reCAPTCHA keys from recaptcha.net
	public static $recaptchaPublic = '';
	public static $recaptchaPrivate = '';
	
	// username encryption vars
	public static $emailIv = '91JJXhuEqBezvwLVSwNQr3ROvjPhMpz9O9I5j6Ff6Jk=';
	public static $emailKey = 'nsfduoiy9438hrfwd423nfoi4ufh8';
	
	// password hashing vars
	public static $passwordGlobalSalt = 'fjw9408h3wofi4023fho33049';
}
