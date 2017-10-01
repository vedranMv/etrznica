<?php 
include "connFile.php";

function checkSession($cookiemail, $cookieSes)
{
    //  Get user ID based on current session (retreived from cookie)
    $stmt = $conHandle->prepare("SELECT id FROM korisnici WHERE (emailStr = ?) AND (session = ?)") or die("Error binding");
    $stmt->bind_param("ss", $cookiemail, $cookieSes);
    $stmt->execute();
    
    $stmt->bind_result($userid);
    
    $exists = false;
    while($stmt->fetch())
    {
        $exists = true;
        break;
    }
    
    
    
    if ($exists === TRUE)
    {
        $retVal = $userid;
    }
    else {
        $retVal = (-1);
    }
    $stmt->close();
    return $retVal;
}

$vrr = checkSession($_COOKIE['mojDucan_username'], $_COOKIE['mojDucan_session']);

echo $vrr;

?>