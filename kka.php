<?php
/*
	Knock Knock Admin by Luciano Laporta Podazza (luciano (At) hack-it * com * ar)
*/

//Configuration

//	Configure your passphrase, remember that the white space is the separator.
//	Examples:
//	"an example" -- 2 words, 2 tries to succeed.
//	"follow the white rabbit" -- 4 words, 4 tries to succeed.
//	"this example contains numbers 123456" -- 5 words, 5 tries to succeed.
//	"password1,password2,password3" -- 1 word(there's no white space separator), 1 try to succeed.
//	Please note that passphrase IS case sensitive! (PASSWORD is not equal to password)
define('PASSPHRASE',		'abra kadabra');

// The word to close your session. It's goes in only one request
define('CLOSE_SESSION',		'close it');

// The file used to save sessions. Don't touch this without being sure of what are you doing!
define('FILE_NAME', 		hash('sha256', PASSPHRASE . hash('sha256', $_SERVER['REMOTE_ADDR'])).'-kka');

//  Configure the loging options.
//	The level can be:
//	- ALL: Log every request
//	- SUCCESSFULL: Log only successfull attempts
//	- NONE: Log nothing
//	This is aplicable to both email and file loging
define('LOG_FILE_LEVEL',	'ALL');
define('LOG_FILE_NAME',		'log-' . date('d-m-Y-') . FILE_NAME . '.log');
define('LOG_EMAIL_LEVEL',	'NONE');
define('LOG_EMAIL_ADDRESS',	'example@example.com'); 
// Stop editing here!!


//We get the knock (i.e. /path/?pass we get the "pass" string.)
if(isset($_SERVER['QUERY_STRING']) && !empty($_SERVER['QUERY_STRING'])) {
	$knock = urldecode($_SERVER['QUERY_STRING']);
    log_knock('ALL');
} elseif(isset($_SERVER['REQUEST_URI']) && !empty($_SERVER['REQUEST_URI']) && strpos('?',$_SERVER['REQUEST_URI']) !== false) {
	$knock = urldecode(end(explode('?', $_SERVER['REQUEST_URI'], 2)));
    log_knock('ALL');
} else {
	$knock = '';
}


//Close the session
if($knock == CLOSE_SESSION) {
		unlink(FILE_NAME);
		header('HTTP/1.1 404 Not Found');
		exit();
}

// Not the first try?
if(file_exists(FILE_NAME)) {
	$succeed_try = file_get_contents(FILE_NAME); //Counter to match with $possible_tries
}else{
	$succeed_try = 0;
}

//Trim starting/ending whitespace and create an array of passwords
$passphrase	= explode(' ', trim(PASSPHRASE));
	
//Still not in?
if($succeed_try != count($passphrase)) {

	if($knock == $passphrase[$succeed_try]) {	
		// Correct
        log_knock('SUCCESSFULL');
		file_put_contents(FILE_NAME, $succeed_try +1);
	} else {
		// Start again
		file_put_contents(FILE_NAME, 0);
	}
	header('HTTP/1.1 404 Not Found');
	exit();
}

// Return timestamp, request, IP and user agent for loging
function log_knock($level){
	$HostInfo	 = 'Timestamp: '	. date('d-m-Y G:i:s')."\n";
	$HostInfo	.= 'Request: '		. (isset($_SERVER['REQUEST_URI']) 				? $_SERVER['REQUEST_URI'] 				: 'Undefined')		."\n";
	$HostInfo	.= 'Proxy?: '		. (isset($_SERVER['HTTP_X_FORWARDED_FOR']) 		? $_SERVER['HTTP_X_FORWARDED_FOR']		: 'Probably not')	."\n";
	$HostInfo	.= 'IP Address: '	. (isset($_SERVER['REMOTE_ADDR']) 				? $_SERVER['REMOTE_ADDR'] 				: 'Undefined')		."\n";
	$HostInfo	.= 'User-Agent:'	. (isset($_SERVER['HTTP_USER_AGENT']) 			? $_SERVER['HTTP_USER_AGENT'] 			: 'Undefined')		."\n\n";
	
	if(LOG_FILE_LEVEL == $level) {
		log_to_file($HostInfo);
	}
	
	if(LOG_EMAIL_LEVEL == $level){
		log_to_email($HostInfo);
	}
}

function log_to_file($what){
	file_put_contents(LOG_FILE_NAME, $what, FILE_APPEND);
}

function log_to_email($what){
	mail(LOG_EMAIL_ADDRESS, 'Knock Knock Admin log', $what);
}