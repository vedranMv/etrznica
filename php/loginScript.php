<?php 
/**
 * Script handles login requests from front-end
 * It receives user's email and password, validates them and upon successfull
 * validation creates new session for user to use
 */ 
include "connFile.php";
include "sessionManager.php";


//  Fetch all data through POST
$email = "";
if (isset($_POST['email'])) {
    $email = $_POST['email'];
}
    
$passwd = "";        
if (isset($_POST['passwd'])){
    $passwd = $_POST['passwd'];
}
    

$errorMsg = "";

//  If empty arguments are sent skip login, display error message. This same check
//  is performed in JS in front-end, but there's never enough caution
if (!(isEmptyStr($email) || isEmptyStr($passwd)))
{
    //  Encrypt password for comparison with DB
    $email = $aesEngine->encrypt($email);
    $email = base64_encode($email);
    //  Check if user exists in database
    $stmt = $conHandle->prepare("SELECT passwordStr FROM korisnici WHERE emailStr = ?") or die("Error binding");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    
    $stmt->bind_result($passK);
    $stmt->store_result();
    
    //  Save username in cookies, cookie expires after 1 day
    setcookie($usernameCookie,$email,(time()+24*60*60), '/', $domainName, TRUE, TRUE);
    $exists = ($stmt->num_rows() > 0);
    
    $stmt->fetch();
    
    //  User doesn't exist
    if (!$exists) {
        echo 'Neispravna kombinacija korisničkog imena i lozinke, pokušajte ponovo.';
        return;
    }
    
    //  We've confirmed user exists, lat's verify its password
    if (password_verify($passwd, $passK)) {
        //  Success
        $errorMsg = 'Prijava uspješna, nakon preusmjerenja možete početi s korištenjem stranice.';
        //  Create new session for user
        createNewSessionFor($email);

    } else {
        //  Password validation failed
        $errorMsg = 'Neispravna kombinacija korisničkog imena i lozinke, pokušajte ponovo.';
    }
    
}
else {
    $errorMsg = "Neispravna kombinacija korisničkog imena i lozinke, pokušajte ponovo.";
}

echo $errorMsg;
?>

