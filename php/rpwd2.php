<?php 
/**
 * Reset user's password in DB
 * This script receives through POST new password for a user to save in DB from
 * HTML. It then verifies that password is long enough, it matches repeated 
 * password and that password-reset link didn't expire. Upon successuful
 * validation password is salted, hashed using Bcrypt and stored in DB.
 */
require_once "connFile.php";
require_once "sessionManager.php";

//  Fetch all 4 parameters sent through POST
$email = "";
if (isset($_POST['email'])) {
    $email = $_POST['email'];
}

$pwd = "";
if (isset($_POST['passwd'])) {
    $pwd = $_POST['passwd'];
    //  Perform length-check for password, min 6 characters
    if (strlen($pwd) < 6) {
        echo "Lozinka mora biti duža od 6 znakova!";
        return;
    }
}

$pwd2 = "";
if (isset($_POST['passwd2'])) {
    $pwd2 = $_POST['passwd2'];
}

$token = "";
if (isset($_POST['id'])) {
    $token = $_POST['id'];
}

//  Do check on empty parameters - again JS should do it on user side by there's
//  a chance of user tampering with script so it's done on server side as well
if (!(isEmptyStr($email) || isEmptyStr($pwd) || 
      isEmptyStr($pwd2) || isEmptyStr($token))) {
    //Enter this branch only if all parameters are supplied
    
  
    //  Check if passwords match, the cheapest operation
    if ($pwd !== $pwd2) {
        echo "Unešene lozinke se moraju podudarati!";
        return;
    }
    
    //  Check if user exists in DB
    $uidDB = emailDBToUID($email);
    if ($uidDB === (-1)) {
        echo "Neispravna e-mail adresa!";
        return;
    }
    
    //  Check validity of token: 
    //      (if it matches user id from e-mail) AND (it didn't expire)
    $stmt = $conHandle->prepare("SELECT userid, expiration FROM pwdrequests WHERE (token = ?)");
    //  Bind parameters for prepared statement
    $stmt->bind_param("s", $token);
    $ret = $stmt->execute();
    
    $stmt->bind_result($uidTok, $expTok);
    $stmt->fetch();
    
    $stmt->close();
    
    if ($uidDB !== $uidTok) {
        //  There's a missmatch, we're trying to reset a password for a wrong 
        //  email -> someone tampered with hash sent through AJAX
        echo "Dogodila se greška, molimo pokušajte ponovno.";
        return;
    }
    
    $timeNow = new DateTime('NOW');
    $timeExp = new DateTime($expTok);
    //  Here we check if link for resetting password has expired
    if ($timeNow > $timeExp) {
        echo 'Vaš link za ponovno postavljanje lozinke je istekao, molimo <a href="?page=resetPwd">zatražiti novi link</a>.';
        return;
    }
    
    //--------------------------------------------------------------------------
    //  User exists; We have valid token; Token is connected to the correct
    //  user account; Passwords are not empty and they match
    //      -> everything is ready for restting the password
    //--------------------------------------------------------------------------
    
    //  Setup options used to encrypt user's password
    $options = [
        'cost' => 11,
        'salt' => mcrypt_create_iv(22, MCRYPT_DEV_URANDOM),
    ];
    // Link parameters for parametric expression
    $pwdP   = password_hash($pwd, PASSWORD_BCRYPT, $options);
    $saltP  = $options['salt'];
    //  Save new password to database
    $stmt = $conHandle->prepare("UPDATE korisnici SET passwordStr = ?, saltStr = ? WHERE (id = ?)") or die("Error binding");
    $stmt->bind_param("sss", $pwdP, $salt, $uidDB);
    
    // Update product info in database
    $ret = $stmt->execute();
    
    if ($ret === TRUE) {
        echo 'Lozinka je uspješno izmijenjena, sada se <a href="?page=login">možete prijaviti</a>';
        
        //  Delete from DB all tokens belonging to this user
        $stmt = $conHandle->prepare("DELETE FROM pwdrequests WHERE (userid = ?)");
        $stmt->bind_param("i", $uidDB);
        $ret = $stmt->execute();
        
    } else {
        echo "Dogodila se greška, molimo pokušajte ponovno.";
    }
    
    $stmt->close(); 
    
} else {
    echo "Molimo vas popunite sva polja!";
}

?>