<?php
	ini_set('display_errors',1);
	ini_set('display_startup_errors',1);
	error_reporting(E_ALL ^ E_STRICT);
	
	if(!isset($pathCheck))
	{	
		require_once ('../globals.php');
	}
	
/*
TODO:
- Implement an email queue, possibly using:
---- https://github.com/seatgeek/djjob
---- Cronjobs
---- ????
---- Magic?
*/

/**
 * Email Handler knows how to user 3 emailing engines, PHPMailer, Mail.php, and sendmail.exe
 * It attempts to use those in order.
 * @author Arthur Cavallari
 *
 */
class EmailHandler
{
	public $from;
	public $to;
	public $cc;
	public $bcc;
	public $subject;
	public $body;
	public $headers;
	
	/**
	 * Constructs a new EmailHandler object
	 * @param string $from
	 * @param string $to
	 * @param string $cc
	 * @param string $bcc
	 * @param string $subject
	 * @param string $body
	 * @param string $headers
	 */
	function __construct($from, $to, $cc, $bcc, $subject, $body, $headers)
	{
		$this->from = $from;
		$this->to = $to;
		$this->cc = $cc;
		$this->bcc = $bcc;
		$this->subject = $subject;
		$this->body = $body;
		$this->headers = $headers;
	}

	function __destruct()
	{
		
	}

	
	/**
	 * Attempts to send the email using using PHPMailer, Mail.php and sendmail.exe
	 * @return string|boolean TRUE on success, FALSE or an error message on fail.
	 */
	public function send()
	{
		if((include 'PHPMailer/class.phpmailer.php'))
		{

			$mail = new PHPMailer();
			
			$mail->IsSMTP();  // telling the class to use SMTP
			$mail->Host     	= $GLOBALS['email_smtp']; // SMTP server
			$mail->Port     	= $GLOBALS['email_smtp_port'];
			$mail->SMTPSecure 	= $GLOBALS['email_smtp_auth_method'];
			$mail->SMTPAuth   	= $GLOBALS['email_smtp_auth'];
			$mail->Username   	= $GLOBALS['email_address'];
			$mail->Password   	= $GLOBALS['email_password'];
			$mail->SetFrom($this->from, $GLOBALS['email_sender_name']);
			$mail->AddReplyTo($this->from, $GLOBALS['email_sender_name']);
			$mail->From     	= $this->from;
			$mail->AddAddress($this->to);
			
			$mail->Subject  	= $this->subject;
			$mail->Body     	= $this->body; // You can put HTML tags in this string
			$mail->WordWrap 	= 50;
			$mail->IsHTML(true); // This allows you to use HTML in the body
			
			if(!$mail->Send()) 
			{
			  //echo 'Message was not sent.';
			  return 'Mailer error: ' . $mail->ErrorInfo;
			} 
			else 
			{
			  return true;
			}
		}
		else if ((include 'Mail.php'))
		{
			echo 'PEAR';
				
			$headers["From"]    = $this->from; 
			$headers["To"]      = $this->to; 
			$headers["Subject"] = $this->subject; 
			$headers["Cc"] 		= $this->cc;
			$headers["Bcc"] 	= $this->bcc;
			$body = $this->body; 
			
			
			$params["host"] = $GLOBALS['email_smtp']; 
			$params["port"] = $GLOBALS['email_smtp_port'];
			$params["auth"] = $GLOBALS['email_smtp_auth']; 
			$params["username"] = $GLOBALS['email_address'];
			$params["password"] = $GLOBALS['email_password'];
			
			
			/*
			$params["host"] = "smtp.mailinator.com"; 
			$params["port"] = "25"; 
			$params["auth"] = false; 
			$params["username"] = ""; 
			$params["password"] = ""; 
			*/
			// Create the mail object using the Mail::factory method 
			$mail_object = new Mail();
			//$mail_object =& Mail::factory("smtp", $params); 
			
			
			if(@$mail_object->send(NULL, $headers, $body))
			{
				return true;					
			}
			else
			{
				return false;	
			}
		}
		else
		{
			echo 'sendmail.exe';
			// To send HTML mail, the Content-type header must be set
			$headers  = 'MIME-Version: 1.0' . "\r\n";
			$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
			
			// Additional headers
			$headers .= 'To: '   . $this->to   . "\r\n";
			$headers .= 'From: ' . $this->from . "\r\n";
			$headers .= 'Cc: '   . $this->from . "\r\n";
			$headers .= 'Bcc: '  . $this->bcc  . "\r\n";
			$headers .= $this->headers . "\r\n";
			if(mail($this->to, $this->subject, $this->body, $this->headers))
			{
				return true;					
			}
			else
			{
				return false;	
			}
		}
	}
}
?>