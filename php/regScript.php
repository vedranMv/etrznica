<?php 
/**
 *  Registration of new users to the system
 *  This script receives parameters about new user to put in the DB through POST
 *  request from HTML. Verification of parameters is assumed to be done on the
 *  front-end, here we only check for empty username and password. When saving to
 *  database, both username is encrypted using AES(declared in connFile.php) and
 *  password is hashed with password_hash() function
 */
require_once "connFile.php";
require_once "zupLookup.php";

//  Fetch all data sent through POST
$email = "";
if (isset($_POST['email'])) {
    $email = $_POST['email'];
}
    
$passwd = "";        
if (isset($_POST['passwd'])) {
    $passwd = $_POST['passwd'];
    //  Perform length-check for password, min 6 characters
    if (strlen($passwd) < 6) {
        echo "Lozinka mora biti duža od 6 znakova!";
        return;
    }
}

$passwd2 = "";
if (isset($_POST['passwd2'])) {
    $passwd2 = $_POST['passwd2'];
}

$naziv = "";
if (isset($_POST['naziv'])) {
    $naziv = $_POST['naziv'];
}

$kontakt = "";
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
        

//  If anything managed to slip-pass JS empty, catch it here
if (!(isEmptyStr($email) || isEmptyStr($passwd) || isEmptyStr($passwd2) ||
    isEmptyStr($naziv) || isEmptyStr($kontakt) || isEmptyStr($mjesto)))
{
    //  Check if passwords match, the most cheapest operation
    if ($passwd !== $passwd2) {
        echo "Unešena i ponovljena lozinka se ne podudaraju.";;
        return;
    }
    
    //  From this point on email is handled exclusively in encrypted form
    $email = base64_encode($aesEngine->encrypt($email));
    //  First check if there is a user already in the database with this email
    $stmt = $conHandle->prepare("SELECT emailStr FROM korisnici WHERE emailStr = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->bind_result($emailK);
    $stmt->store_result();

    //  If non-zero number of rows is returned, user already exists
    $exists = ($stmt->num_rows() > 0);
    $stmt->close();
    
    //  Stop here if we already have user with this email
    if ($exists) {   
        echo "Korisnik s navedenom e-mail addresom već postoji, ukoliko ste zaboravili svoju lozinku probajte istu resetirati.";
        return;
    }
    
    //  Proceed with insertion of user into the database
    $stmt = $conHandle->prepare("INSERT INTO korisnici(emailStr, passwordStr, saltStr, naziv, kontakt, datumReg, zupanija, mjesto, zupanijaStr) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    //  Setup options used to encrypt user's password
    $options = [
        'cost' => 11,
        'salt' => mcrypt_create_iv(22, MCRYPT_DEV_URANDOM),
    ];
    // Link parameters for parametric expression
    $pwdP   = password_hash($passwd, PASSWORD_BCRYPT, $options);
    $saltP  = $options['salt'];
    $datP   = date("d-m-Y");

    
    //  Bind parameters for prepared statement
    $stmt->bind_param("ssssssiss", $email, $pwdP, $saltP, $naziv, $kontakt, $datP, $zup, $mjesto, $zupanije[$zup] );
    $ret = $stmt->execute();
    
    if ($ret === TRUE) {   //  Successful execution of INSERT query
        //  Save username in cookies, cookie expires after 1 day
        setcookie($usernameCookie,$email,(time()+24*60*60), '/');
        $errorMsg .= 'Uspješno ste se registrirali. Sada se možete <a href="?page=login">prijaviti u sustav</a>';
    }
    else {   //  Execution of INSERT query failed
        $errorMsg .= "Dogodila se greška kod registracije";
    }
    
    $stmt->close();
}
else {
    $errorMsg .= "Molimo ispravno popunite sva obavezna polja označena *";
}

echo $errorMsg;
?>


