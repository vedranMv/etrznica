<?php 
/**
 * Update user settings
 * This script receives data from a HTML form through POST. Upon successfull
 * validation user data in DB is updated with the one received here.
 */
require_once "connFile.php";
require_once "zupLookup.php";
require_once "sessionManager.php";

//  Fetch all data sent through POST
$newEmail = "";
if (isset($_POST['email'])) {
    $newEmail = $_POST['email'];
}

$passwd = "";
if (isset($_POST['passwd'])) {
    $passwd = $_POST['passwd'];
}

$passwd2 = "";
if (isset($_POST['passwd2'])) {
    $passwd2 = $_POST['passwd2'];
}

$naziv="";
if (isset($_POST['naziv'])) {
    $naziv= $_POST['naziv'];
}
    
if (isset($_POST['kontakt'])) {
    $kontakt = $_POST['kontakt'];
}

$zup = 22;
if (isset($_POST['zupanija'])) {
    $zup = $_POST['zupanija'];
}

$mjesto = "";
if (isset($_POST['mjesto'])) {
    $mjesto = $_POST['mjesto'];
}

//  Check for valid session
if (!hasValidSession()) {
    echo "Dogodila se greška, molimo pokušajte ponovno.";
    return;
}
//  Now that we have verified data in cookies we can use it for DB queries
$cookieEmail = getUserCookie();
$cookieSession = getSessionCookie();
$errorMsg = "";

$deauth = FALSE;
$ret = TRUE;

//  Check if user required change of password and supplied non-empty password
if (($passwd2 === $passwd) && !isEmptyStr($passwd))
{
    //  Perform length-check for password, min 6 characters
    if (strlen($passwd) < 6) {
        $errorMsg .= "Lozinka mora biti duža od 6 znakova!<br/>";
    } else {  
        //  Setup options used to encrypt user's password
        $options = [
            'cost' => 11,
            'salt' => mcrypt_create_iv(22, MCRYPT_DEV_URANDOM),
        ];
        // Link parameters for parametric expression
        $pwdP   = password_hash($passwd, PASSWORD_BCRYPT, $options);
        //$pwd2P  = password_hash($passwd2, PASSWORD_BCRYPT, $options);
        $saltP  = $options['salt'];
        
        $stmt = $conHandle->prepare("UPDATE korisnici SET passwordStr = ?, saltStr = ? WHERE (emailStr = ?) AND (session = ?)") or die("Error binding");
        $stmt->bind_param("ssss", $pwdP, $salt, $cookieEmail, $cookieSession);
        
        // Update product info in database
        $ret = $stmt->execute();
        
        if ($ret === TRUE) {
            $errorMsg .= "Lozinka je uspješno izmijenjena<br/>";
        }
    
        $stmt->close();   
        //  If user changes password, performe deauthorization once data is saved to
        //  force user to re-login
        $deauth = TRUE;
    }
}
else if ($passwd2 !== $passwd)  {
    $errorMsg .= "Lozinke se ne podudaraju ili su prazne, nije dozvoljeno.<br/>";
}

//  If new email is non-empty, encrypt & encode it so it can be stored in DB
//  else use value from a cookie in while perform UPDATE query
if ($newEmail !== "") {
    $newEmail = base64_encode($aesEngine->encrypt($newEmail));
} else {
    $newEmail = getUserCookie();
}
//  Check if user actually supplied new email or just his old one that was
//  auto-inserted in the settings
if ($newEmail !== getUserCookie()) {
    //  Change value of username in cookies so that session can be validated
    //  (cookie expires after 1 day)
    setcookie($usernameCookie,$newEmail,(time()+24*60*60), '/');
    //  If user changes username, performe deauthorization once data is saved to
    //  force user to re-login
    $deauth = TRUE;
}

$stmt = $conHandle->prepare("UPDATE korisnici SET emailStr = ?, naziv = ?, kontakt = ?, zupanija = ?, mjesto = ?, zupanijaStr = ?  WHERE (emailStr = ?) AND (session = ?)") or die("Error binding");
$zupStr = $zupanije[$zup];
$stmt->bind_param("sssissss", $newEmail, $naziv, $kontakt, $zup, $mjesto, $zupStr, $cookieEmail, $cookieSession);

// Update user info in database
$ret = $ret && ($stmt->execute());
$stmt->close();


if ($ret) {
    if ($errorMsg === ""){
        $errorMsg .= "Vaši su podaci uspješno spremljeni.<br/>";
    } else {
        $errorMsg .= "Ostali su podaci uspješno spremljeni.<br/>";
    }
    
} else {
    $errorMsg .= "Dogodila se greška prilikom spremanja podataka, molimo poušajte kasnije<br/>";
}

//  If user has changed password or email, destroy its current session and require re-login
if ($deauth) {
    destroyCurrentSession();
}

echo $errorMsg;
?>

