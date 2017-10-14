<?php 
/**
 * Send password-reset email to user
 * This script receives through POST an email address from HTML form. It 
 * validates the address and sends out a passowrd-resetting email to that address. 
 */
require_once "connFile.php";
require_once "sessionManager.php";

//  Fetch all data through POST
$email = "";
if (isset($_POST['email'])) {
    $email = $_POST['email'];
}


//  Sanity check, javascript should handle this case but still, for curious ones
if ($email !== "") {
    //  Create temporary identification token which will serve as identification
    //  method for this user. Valid for 10 minutes
    $token = md5(time());
    $exp = new DateTime('NOW'); 
    $exp->add(new DateInterval("PT10M"));   //  Add 10 min from current time
    $exp = $exp->format('Y-m-d H:i:s');     //  Date in format yyyy-mm-dd hh:mm:ss
    
    //  Make sure user exists
    $uid = emailDBToUID($email);
    if ($uid === (-1)) {
        echo "Zahtjev za ponovno postavljanje lozinke je poslan na ovaj email ukoliko on postoji.";
        return;
    }
    
    //  Delete from DB all tokens belonging to this user
    $stmt = $conHandle->prepare("DELETE FROM pwdrequests WHERE (userid = ?)");
    //  Bind parameters for prepared statement
    $stmt->bind_param("i", $uid);
    $ret = $stmt->execute();
    
    //  Save new token to DB
    $stmt = $conHandle->prepare("INSERT INTO pwdrequests(userid, token, expiration) VALUES (?, ?, ?)");
    //  Bind parameters for prepared statement
    $stmt->bind_param("iss", $uid, $token, $exp);
    $ret = $stmt->execute();
    
    //  Send email to user explaining the procedure of resetting password
    $subject = 'Postavljanje nove lozinke';
    
    $headers[] = 'MIME-Version: 1.0';
    $headers[] = 'Content-type: text/html; charset=iso-8859-1';
    // Additional headers
    //$headers[] = 'To: Vedran <vedran.mikov@gmail.com>';
    $headers[] = 'From: E-tržnica podrška <e.trznica.info@gmail.com>';
    
    // The message
    $message = '
<!DOCTYPE html>
<html >
    <head>
      <title>Postavljanje nove lozinke</title>
    </head>
    <body style="max-width=500px;">
    <table style="max-width=500px;">
        <tr>
            <td>
                <img alt="" src="https://e-trznica.duckdns.org/imgs/title.png"/>
            </td>
        </tr>
        <tr>
            <td>
                <h2>Zaprimili smo zahtjev za ponovno postavljanje lozinke</h2>
<p style="font-size:14px;">Poštovani, ovaj mail ste dobili iz razloga što ste je za vaš korisnički račun
na stranici e-Tržnica zatražena nova lozinka. U slučaju da to niste vi zatražili
možete ignorirati ovaj mail i nastaviti koristiti svoju staru lozinku. Ukoliko vam
je potrebna nova lozinka slijediti dolje navedeni link za postavljanje nove lozinke.
Proces ponovno postavljanja lozinke morate dovršiti u sljedećih 10 minuta ili će
biti potrebno zatražiti novi zahtjev.<p>
            </td>
        </tr>
        <tr>
            <td>
                <a href="https://e-trznica.duckdns.org/?page=resetPwd&token='.$token.'">Ovdje postavite novu lozinku</a>
<p>Ukoliko link ne radi otvorite stranicu u vašem web pregledniku: </p>
<p>https://e-trznica.duckdns.org/?page=resetPwd&token='.$token.'</p>
            </td>
        </tr>
    </table>
    </body>
</html>
';
    $attempts = 0;
    if (isset($_COOKIE[$attemptCookie])) {
        $attempts = $_COOKIE[$attemptCookie];
    }
    //  Set cookie that limits reseting e-mail more than 5 times within 1 hour
    setcookie($attemptCookie, $attempts + 1, time()+60*60, '/');
    
    if ($attempts < 2) {
        mail($email, $subject, $message, implode("\r\n", $headers));
    } else {
        echo "Previše the puta zatražili novu lozinku unutar kretkog vremena.
Pričekajte 1 sat pa pokušajte ponovno";
        return;
    }

    
}

echo "Zahtjev za ponovno postavljanje lozinke je poslan na navedeni email ukoliko on postoji.";

?>