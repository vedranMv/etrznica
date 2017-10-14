<?php 

/**
 * Return AES-encrypted email address of a user as stored in a cookie
 * @return AES-encrypted email address from cookies if it exists, empty string
 * otherwise
 */
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

/**
 * Return decrypted email from encrypted value stored in cookies
 * @return Plain-text email of a user as stored in cookies if it exists, empty
 * string otherwise
 */
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

/**
 * Return session hash stored in a user's cookie
 * @return Session hash if cookie exists, empty string otherwise
 */
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
 * Query the database for a provided unencrypted email addres and return 
 * userid if it exists. If it doesn't exist -1 is returned
 * @param Unencrypted email string
 * @return userid matching provided email, (-1) if there's no match
 */
function emailDBToUID($emailString)
{
    global $conHandle, $aesEngine;
    
    $emailB64hash = base64_encode($aesEngine->encrypt($emailString));
    
    $stmt = $conHandle->prepare("SELECT id FROM korisnici WHERE (emailStr = ?)");
    $stmt->bind_param("s", $emailB64hash);
    $stmt->execute();
    
    $stmt->bind_result($userid);
    $stmt->store_result();
    
    $exists = ($stmt->num_rows() > 0);
    
    if ($exists === TRUE) {
        $stmt->fetch();
        return $userid;
    } else {
        return (-1);
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
    global $conHandle;
    
    //  By default, when user logs out his session is set to an empty string,
    //  this would technically allow anyone to hijack a session of a logged-out
    //  user by setting sessionCookie to "" and emailCookie to someone elses
    //  email. This check automatically claims all empty-string session invalid.
    if (isEmptyStr(getSessionCookie()) || isEmptyStr($cookieSes)  || isEmptyStr($cookiemail)) {
        return (-1);
    }
    
    //  Get user ID based on current session (retreived from cookie)
    $stmt = $conHandle->prepare("SELECT id FROM korisnici WHERE (emailStr = ?) AND (session = ?)") or die("Error binding");
    //$stmt->bind_param("ss", $cookiemail, $cookieSes);
    $stmt->bind_param("ss", $cookiemail, $cookieSes);
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
 * Check whether existing cookie data makes up a valid session
 * @return TRUE if session is valid, FALSE if it isn't
 */
function hasValidSession()
{
    //  By default, when user logs out his session is set to an empty string, 
    //  this would technically allow anyone to hijack a session of a logged-out
    //  user by setting sessionCookie to "" and emailCookie to someone elses 
    //  email. This check automatically claims all empty-string session invalid.
    if (isEmptyStr(getSessionCookie()) || isEmptyStr(getUserCookie())) {
        return FALSE;
    }
    $sessionUID = sessionToUID(getUserCookie(), getSessionCookie());
    
    if ($sessionUID === (-1)) {
        return FALSE;
    } else {
        return TRUE;
    }
}

/**
 * Create new session for a user with provided AES-encrypted e-mail
 * @param $email AES-encripted $email uncoded using base64_encode
 * @return none
 */
function createNewSessionFor($email)
{
    global  $conHandle, $sessionCookie, $usernameCookie;
    
    //  Create session as hash of current time
    $session = password_hash(time(), PASSWORD_BCRYPT);
    
    //  Store parameters in cookies
    //Session is valid for 1 hour
    setcookie($sessionCookie, $session, (time()+60*60),'/', $domainName, TRUE, TRUE);
    //Username is saved for 24 hours
    setcookie($usernameCookie, $email, (time()+24*60*60),'/', $domainName, TRUE, TRUE);

    //  Update user's session in database
    $stmt = $conHandle->prepare("UPDATE korisnici SET session = ? WHERE emailStr = ?") or die("Error binding");
    $stmt->bind_param("ss", $session, $email);
    $stmt->execute();
    $stmt->close();
}

/**
 * Destroy session stored in user's cookies and DB
 * @return none
 */
function destroyCurrentSession()
{
    global $conHandle, $sessionCookie;
    
    $emptySession = "";
    //  Kill session stored in a cookie
    setcookie($sessionCookie, $emptySession, time()-3600*24,'/', $domainName, TRUE, TRUE);
    
    //  Update user's session in database
    $stmt = $conHandle->prepare("UPDATE korisnici SET session = ? WHERE emailStr = ?") or die("Error binding");
    $emailRef = getUserCookie();
    $stmt->bind_param("ss", $emptySession, $emailRef);
    $stmt->execute();
    $stmt->close();
}

?>