<?php 
include "connFile.php";

if (isset($_COOKIE['mojDucan_username']))
    $email = $_COOKIE['mojDucan_username'];

//  Chekc if user is logged in and has an opened session
if (isset($_COOKIE['mojDucan_session']))
    setcookie('mojDucan_session', "", time()-5, '/');

    if ($email != "")
    {
        //  Clear user's session from database
        $stmt = $conHandle->prepare("UPDATE korisnici SET session = ? WHERE emailStr = ?") or die("Error binding");
        $session = "";
        $stmt->bind_param("ss", $session, $email);
        $stmt->execute();
    }
?>