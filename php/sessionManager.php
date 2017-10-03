<?php 

function getUserCookie()
{
    global $usernameCookie;
    
    if (isset($_COOKIE[$usernameCookie])) {
        return $_COOKIE[$usernameCookie];
    }
    else {
        return "";
    }
}

function getUserDeHashCookie()
{
    global $usernameCookie, $aesEngine;
    
    if (isset($_COOKIE[$usernameCookie])) {
        return $aesEngine->decrypt(base64_decode($_COOKIE[$usernameCookie],true));
    }
    else {
        return "";
    }
}

function getSessionCookie()
{
    global $sessionCookie;
    if (isset($_COOKIE[$sessionCookie])) {
        return $_COOKIE[$sessionCookie];
    }
    else {
        return "";
    }
}

/**
 * Check whether the arguments match a valid user session from DB
 * @param $cookiemail is email of a user, as stored in cookies
 * @param $cookieSes is session of a user, as stored in cookies
 * @return userid if parameters are valid, else -1
 */
function sessionToUID($cookiemail, $cookieSes)
{
    global $conHandle, $sessionCookie, $usernameCookie;
    //  Get user ID based on current session (retreived from cookie)
    $stmt = $conHandle->prepare("SELECT id FROM korisnici WHERE (emailStr = ?) AND (session = ?)") or die("Error binding");
    //$stmt->bind_param("ss", $cookiemail, $cookieSes);
    $stmt->bind_param("ss", getUserCookie(), getSessionCookie());
    $stmt->execute();
    
    $stmt->bind_result($userid);
    $stmt->store_result();

    $exists = ($stmt->num_rows() > 0);
      
    if ($exists === TRUE)
    {
        $stmt->fetch(); 
        $retVal = $userid;
    }
    else {
        $retVal = (-1);
    }
    $stmt->close();
    return $retVal;
}

/**
 * @param $email AES-encripted $email uncoded using base64_encode
 */
function createNewSessionFor($email)
{
    global  $conHandle, $sessionCookie, $usernameCookie;
    
    //  Create session as hash of current time
    $session = password_hash(time(), PASSWORD_BCRYPT);
    
    //  Store parameters in cookies
    //Session is valid for 1 hour
    setcookie($sessionCookie, $session, (time()+60*60),'/');
    //Username is saved for 24 hours
    setcookie($usernameCookie, $email, (time()+24*60*60),'/');

    //  Update user's session in database
    $stmt = $conHandle->prepare("UPDATE korisnici SET session = ? WHERE emailStr = ?") or die("Error binding");
    $stmt->bind_param("ss", $session, $email);
    $stmt->execute();
    $stmt->close();
}

function destroyCurrentSession()
{
    global $conHandle, $sessionCookie;
    
    $emptySession = "";
    //  Kill session stored in a cookie
    setcookie($sessionCookie, $emptySession, time()-3600*24,'/');
    
    //  Update user's session in database
    $stmt = $conHandle->prepare("UPDATE korisnici SET session = ? WHERE emailStr = ?") or die("Error binding");
    $emailRef = getUserCookie();
    $stmt->bind_param("ss", $emptySession, $emailRef);
    $stmt->execute();
    $stmt->close();
}

?>