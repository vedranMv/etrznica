<?php 

// Turn off all error reporting
error_reporting(0);

/**
 * Check whether an argument is empty string
 * @param $var String to be checked
 * @return TRUE if it's empty string, FALSE if it isn't
 */
function isEmptyStr($var){
    return ($var === "");
}

//  Setup a path to encryption package and initialize encryption engine
set_include_path(get_include_path() . PATH_SEPARATOR . '/var/www/html2/php/phpseclib1.0.7');
require_once ('Crypt/AES.php');

/**
 * Initialization of AES engine
 * AES is used in this case to encrypt email of a user before storing it to DB in
 * case that DB gets compromised whoever obtains its content can't use emails for
 * spamming or revealing identity before obtaining this key as well. Password in 
 * DB are already salted and encrypted with Bcrypt!
 */  
$aesEngine = new Crypt_AES();
//Key used to decrypt/encrypt parameters with AES
$key = "123456789abcdefghijk";
$aesEngine->setKey($key);

$domainName = "e-trznica.duckdns.org";

//  Prefix for stored cookie names
$siteName = "etrznica_";
//Definition of cookie names in string
$usernameCookie = $siteName.'c1';   //Username
$sessionCookie  = $siteName.'c2';   //Session
$attemptCookie  = $siteName.'c3';   //Number of sent password resets in 1h


//  Configuration data for accessign database
$dbUser = "";
$dbName = "";
$dbPwd  = "";
$dbHost = "";

$conHandle = new mysqli($dbHost, $dbUser, $dbPwd, $dbName);

if ($conHandle->connect_errno)
{
    die("Greška u pristupu bazi podataka");
}

Locale::setDefault('O');

$conHandle->set_charset("utf8");
$_GLOBALS['conHandle'] = $conHandle;

//  Seed random number generator
srand(time());

?>