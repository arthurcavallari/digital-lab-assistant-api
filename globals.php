<?php
	/**
	 * Base URL, must be terminated with a forward slash.
	 * If your files are installed under: /users/username/public_html/~username/
	 * then, base URL = "/~username/"
	 */
	$_base_url = "/~username/";
	
	/**
	 * Default Email Sender details, used for all emails sent by the server.
	 */
	$_email_sender_name = "Administrator";
	$_email_username = "admin@example.com";
	$_email_password = "password";
	$_email_smtp = "smtp.gmail.com";
	$_email_smtp_port = "465"; // Usually, 465->SSL, 587->TLS
	$_email_smtp_auth = "true";
	$_email_smtp_auth_method = "ssl"; // SSL or TLS
	

	/**
	 * Absolute path to the server files, must be terminated with a forward slash.
	 * If your files are installed under: /users/username/public_html/~username/
	 * then, absolute path = "/users/username/"
	 */
	$_absolute_path = "/users/username/";
	
	/**
	 * Database name (SCHEMA)
	 */
	$_db_name = "_db_name_";
	
	/**
	 * Database server address/web server address, must be a fully qualified domain name, 
	 * and accessible from the internet. Used when providing users with a reset password link.
	 */
	$_db_server = "127.0.0.1";
	
	/**
	 * Database username and password
	 */
	$_db_username = "_db_user_";
	$_db_password = "_db_password_";


	/********************************************
	 * DO NOT MODIFY ANYTHING BELOW THIS LINE! *
	 ********************************************/
	 
	$GLOBALS['salt'] = 'supds!d00med#%time'; // Do not reuse
	$GLOBALS['debug_mode'] = FALSE;
	$pathCheck = $_SERVER['HTTP_HOST'];
	$host = strtoupper(gethostname());

	$GLOBALS['base_url'] 				= $_base_url; 

	$GLOBALS['email_address'] 			= $_email_username; 
	$GLOBALS['email_sender_name'] 		= $_email_sender_name;
	$GLOBALS['email_password'] 			= $_email_password;
	$GLOBALS['email_smtp'] 				= $_email_smtp;
	$GLOBALS['email_smtp_port'] 		= $_email_smtp_port;
	$GLOBALS['email_smtp_auth'] 		= $_email_smtp_auth;
	$GLOBALS['email_smtp_auth_method'] 	= $_email_smtp_auth_method;
	
	$GLOBALS['db_server'] 				= $_db_server;
	$GLOBALS['db_name']					= $_db_name; 
	$GLOBALS['db_username'] 			= $_db_username; 
	$GLOBALS['db_password'] 			= $_db_password; 
	
	$GLOBALS['absolute_path'] 			= $_absolute_path; 
	
	
	$GLOBALS['server_url'] 				= "http://" . $GLOBALS['db_server'] . $GLOBALS['base_url'];
	
	/**
	 * Formats a JSON string so it's easily readable for humans
	 * @param unknown $json
	 * @return boolean|string
	 */
	function json_format($json) 
	{ 
		$tab = "  "; 
		$new_json = ""; 
		$indent_level = 0; 
		$in_string = false; 
	
		$json_obj = json_decode($json); 
	
		if($json_obj === false) 
			return false; 
	
		$json = json_encode($json_obj); 
		$len = strlen($json); 
	
		for($c = 0; $c < $len; $c++) 
		{ 
			$char = $json[$c]; 
			switch($char) 
			{ 
				case '{': 
				case '[': 
					if(!$in_string) 
					{ 
						$new_json .= $char . PHP_EOL . str_repeat($tab, $indent_level+1); 
						$indent_level++; 
					} 
					else 
					{ 
						$new_json .= $char; 
					} 
					break; 
				case '}': 
				case ']': 
					if(!$in_string) 
					{ 
						$indent_level--; 
						$new_json .= PHP_EOL . str_repeat($tab, $indent_level) . $char; 
					} 
					else 
					{ 
						$new_json .= $char; 
					} 
					break; 
				case ',': 
					if(!$in_string) 
					{ 
						$new_json .= "," . PHP_EOL . str_repeat($tab, $indent_level); 
					} 
					else 
					{ 
						$new_json .= $char; 
					} 
					break; 
				case ':': 
					if(!$in_string) 
					{ 
						$new_json .= ": "; 
					} 
					else 
					{ 
						$new_json .= $char; 
					} 
					break; 
				case '"': 
					if($c > 0 && $json[$c-1] != '\\') 
					{ 
						$in_string = !$in_string; 
					} 
				default: 
					$new_json .= $char; 
					break;                    
			} 
		} 
	
		return $new_json; 
	} 
	
	/**
	 * Checks if a given value is null or empty string
	 * @param unknown $str
	 * @return boolean
	 */
	function IsNullOrEmptyString($str)
	{
    	return (!isset($str) || trim($str) === '');
	}
?>