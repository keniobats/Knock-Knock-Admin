<?php
/*
Knock Knock Admin by Luciano Laporta Podazza (luciano (At) hack-it * com * ar)

Configure your passphrase, remember that the white space is the separator.
Examples:
"an example" -- 2 words, 2 tries to succeed.
"follow the white rabbit" -- 4 words, 4 tries to succeed.
"this example contains numbers 123456" -- 5 words, 5 tries to succeed.
"password1,password2,password3" -- 1 word(there's no white space separator), 1 try to succeed.
Please note that passphrase IS case sensitive! (PASSWORD is not equal than password)
*/
//Configuration
$denied_path		 			  = "/access/"; //Path to protect.
$passphrase			 			  = "memento mori";  //Your passphrase.
$filename_hash					  = sha1( $passphrase.sha1( $_SERVER['REMOTE_ADDR'] ) )."-kka";
$passphrase 		 			  = ltrim( $passphrase ); //Trim space at the begining of the string
$passphrase 		 			  = rtrim( $passphrase ); //Trim space at the end of the string
$passphrase 					  = explode(" ", $passphrase ); //We create an array of passwords
$possible_tries			      	  = count( $passphrase ); //Getting the max quantity of tries.
$banning 						  = 10; //Maximum amount of tries before getting attacker's IP address banned.
//We get the knock (i.e. /path/?pass we get the "pass" string.)
$knock = explode("?", $_SERVER['REQUEST_URI'], 2);
/* 
Checking session, if it doesn't exists then we check that /path/ 
hasn't any passphrase or "?" without vars and return 404.
*/
if( file_exists( $filename_hash ) ) {
	if( file_get_contents( $filename_hash ) != $possible_tries) {
		if( $_SERVER['REQUEST_URI'] == $denied_path ) {
		header('HTTP/1.1 404 Not Found');
		exit();
		}
		elseif ( !$knock[1] ) {
		header('HTTP/1.1 404 Not Found');
		exit();
	}
}
}
//End checking
//File checks
if( $handle = dir('.') ) {
	while( false !== ($file = $handle->read()) ) {
		if(substr($file, -4) == "-kka" ) {
			$old_file = $file; //we get the previus generated file.
		}
	}
	$handle->close();
}
$old_file_exploded = explode("-kka", $old_file); 
$new_file = explode("-kka", $filename_hash); 
//Checking that there's an old file and deleting it.
if( file_exists( $old_file) ){
	if( ($old_file_exploded[0] !== $new_file[0]) ) {
	unlink($old_file); 
}	
}
//If it's a new file, initialize it.
if( !file_exists($filename_hash) ) {
	file_put_contents( $filename_hash, "0");	
}
$succeed_try = file_get_contents( $filename_hash ); //Counter to match with $possible_tries
//Let's Knock N' Roll! :) 
if( $succeed_try != $possible_tries ) {
	if( $knock[1] == $passphrase[$succeed_try] ) {
		$succeed_try = $succeed_try + 1;
		file_put_contents( $filename_hash, $succeed_try );
		header('HTTP/1.1 404 Not Found');
		exit();
	}
	else{
		$succeed_try = 0;
		file_put_contents( $filename_hash, $succeed_try );
		header('HTTP/1.1 404 Not Found');
		exit();
	}
}
?>