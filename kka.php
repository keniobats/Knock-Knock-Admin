<?php
/*
Knock Knock Admin by Luciano Laporta Podazza (luciano (At) hack-it * com * ar)

Configure your passphrase, remember that the white space is the separator.
Examples:
"an example" -- 2 words, 2 tries to succeed.
"follow the white rabbit" -- 4 words, 4 tries to succeed.
"this example contains numbers 123456" -- 5 words, 5 tries to succeed.
"password1,password2,password3" -- 1 word(there's no white space separator), 1 try to succeed.
Please note that passphrase IS case sensitive! (PASSWORD is not equal to password)
*/

//Configuration
$passphrase		= 'abra kadabra';				//Your passphrase.
$close_session	= 'close it';					//the word to close your session.
$filename_hash	= hash('sha256', $passphrase . hash('sha256', $_SERVER['REMOTE_ADDR'])).'-kka';

$log			= false;						//Do we log attempts? 1 or 0.
$log_file		= 'log-' . date('d-m-Y-') . $filename_hash . '.log';
// Stop editing here!!

// Is log activated?
if($log) {
	// let's log!, Request, IP, timestamp and user-agent by now...
	$HostInfo	 = 'Request: '		. (isset($_SERVER['REQUEST_URI'])?$_SERVER['REQUEST_URI']:'Undefined')."\n";
	$HostInfo	.= 'Proxy?: '		. (isset($_SERVER['HTTP_X_FORWARDED_FOR'])?$_SERVER['HTTP_X_FORWARDED_FOR']:'Probably not')."\n";
	$HostInfo	.= 'IP Address: '	. (isset($_SERVER['REMOTE_ADDR'])?$_SERVER['REMOTE_ADDR']:'Undefined')."\n";
	$HostInfo	.= 'Timestamp: '	. date('d-m-Y G:i:s')."\n";
	$HostInfo	.= 'User-Agent:'	. (isset($_SERVER['HTTP_USER_AGENT'])?$_SERVER['HTTP_USER_AGENT']:'Undefined')."\n\n";
	file_put_contents($log_file, $HostInfo, FILE_APPEND);
}

//We get the knock (i.e. /path/?pass we get the "pass" string.)
if(isset($_SERVER['QUERY_STRING']) && !empty($_SERVER['QUERY_STRING'])) {
	$knock = urldecode($_SERVER['QUERY_STRING']);
} elseif(isset($_SERVER['REQUEST_URI']) && !empty($_SERVER['REQUEST_URI']) && strpos('?',$_SERVER['REQUEST_URI']) !== false) {
	$knock = urldecode(end(explode('?', $_SERVER['REQUEST_URI'], 2)));
} else {
	$knock = '';
}


//Close the session
if($knock == $close_session) {
		unlink($filename_hash);
		header('HTTP/1.1 404 Not Found');
		exit();
}

// Not the first try?
if(file_exists( $filename_hash)) {
	$succeed_try = file_get_contents($filename_hash); //Counter to match with $possible_tries
}else{
	$succeed_try = 0;
}

//Still not in?
if($succeed_try != count($passphrase)) {
	//Trim starting/ending whitespace and create an array of passwords
	$passphrase	= explode(' ', trim($passphrase));
	
	if($knock == $passphrase[$succeed_try]) {	
		// Correct
		file_put_contents($filename_hash, $succeed_try +1);
	} else {
		// Start again
		file_put_contents($filename_hash, 0);
	}
	header('HTTP/1.1 404 Not Found');
	exit();
}
