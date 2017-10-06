<?php 
require_once "connFile.php";
require_once "zupLookup.php";
require_once "sessionManager.php";

//  Fetch all data sent through POST
$email = "";
if (isset($_POST['email'])) {
    $email = $_POST['email'];
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

$origEmail = getUserCookie();
$session = getSessionCookie();
$errorMsg = "";

$deauth = FALSE;
$ret = TRUE;

//  Check if user required change of password
if (($passwd2 === $passwd) && ($passwd !== ""))
{
    //  Setup options used to encrypt user's password
    $options = [
        'cost' => 11,
        'salt' => mcrypt_create_iv(22, MCRYPT_DEV_URANDOM),
    ];
    // Link parameters for parametric expression
    $pwdP   = password_hash($passwd, PASSWORD_BCRYPT, $options);
    $pwd2P  = password_hash($passwd2, PASSWORD_BCRYPT, $options);
    $saltP  = $options['salt'];
    
    $stmt = $conHandle->prepare("UPDATE korisnici SET passwordStr = ?, saltStr = ? WHERE (emailStr = ?) AND (session = ?)") or die("Error binding");
    $stmt->bind_param("ssss", $pwdP, $salt, $origEmail, $session);
    
    // Update product info in database
    $ret = $stmt->execute();

    $stmt->close();   
    //  If user changes password, performe deauthorization once data is saved to
    //  force user to re-login
    $deauth = TRUE;
}
else if ($passwd2 !== $passwd) {
    $errorMsg .= "Lozinke se ne podudaraju ili su prazne, nije dozvoljeno.<br/>";
}

$email = $email = base64_encode($aesEngine->encrypt($email));

if ($email !== getUserCookie()) {
    //  Change value of username in cookies so that session can be validated
    //  (cookie expires after 1 day)
    setcookie($usernameCookie,$email,(time()+24*60*60), '/');
    //  If user changes username, performe deauthorization once data is saved to
    //  force user to re-login
    $deauth = TRUE;
}

$stmt = $conHandle->prepare("UPDATE korisnici SET emailStr = ?, naziv = ?, kontakt = ?, zupanija = ?, mjesto = ?, zupanijaStr = ?  WHERE (emailStr = ?) AND (session = ?)") or die("Error binding");
$zupStr = $zupanije[$zup];
$stmt->bind_param("sssissss", $email, $naziv, $kontakt, $zup, $mjesto, $zupStr, $origEmail, $session);

// Update user info in database
$ret = $ret && ($stmt->execute());
$stmt->close();


if ($ret) {
    if ($errorMsg === ""){
        $errorMsg .= "Vaši su podaci uspješno izmijenjeni.";
    } else {
        $errorMsg .= "Ostali su podaci uspješno spremljeni.";
    }
    
} else {
    $errorMsg .= "Dogodila se greška prilikom spremanja podataka, molimo poušajte kasnije";
}

//  If user has logged out, destroy its current session and require re-login
if ($deauth) {
    destroyCurrentSession();
}

echo $errorMsg;
?>

