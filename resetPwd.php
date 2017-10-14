<?php 
require_once "php/connFile.php";
/**
 * This files performs reset of user's password. It uses value of 'token'
 * property passed through GET in order to switch between these pages:
 * token too short: Show page to input e-mail for which we're resetting password
 * token invalid: Show error page
 * token valid: Show form with two password fields, upon validation save PWD in DB
 */

//IMPORTANT: Token is passed as a global variable from index.php file
global $token;

if (strlen($token) !== 32) {
    //  Show form for inputing e-mail and requesting mail with new passwd
    echo '
<h2>Zaboravljena lozinka?</h2>
<p>Upišite e-mail adresu koju koristite za prijavu na ovu stranicu i link za
izmjenu lozinke će vam biti poslan e-mailom</p>
<form id="form_rpwd"  method="post" action="php/rpwd.php" >
    <table>
    <tr>
        <td>Email</td>
        <td>
            <input required="required" onKeyPress="submitByEnter(event, this.form)" type="email" name="email" value="'.(isset($_COOKIE[$usernameCookie])?getUserDeHashCookie():"").'" placeholder="Vaša e-mail adressa"/>
        </td>
    </tr>
</table>
        
	<br/>
	<br/>
	<input type="button" class="button_generic" onclick="submitForm(this.form)" name="reqPwd" value="Zatraži lozinku"  />
	<br/>
	<div id="form_rpwd_status">
	</div>
	<br/>
        
</form>
';
} else {
    //  First verify if we have this hash in table of reset password requests, in
    //  case someone decides to tamper with hash
    $stmt = $conHandle->prepare("SELECT userid, expiration FROM pwdrequests WHERE (token = ?)");
    $stmt->bind_param("s", $token);
    $ret = $stmt->execute();
    
    $stmt->bind_result($uid, $expTok);
    $stmt->store_result();
    $stmt->fetch();
    
    $exists = ($stmt->num_rows() > 0);
    
    //  Check for a match between URI token and DB token
    if ($exists === TRUE){
        echo '<h2>Postavljanje nove lozinke</h2>';
        
        //  Here we check if link for resetting password has expired
        $timeNow = new DateTime('NOW');
        $timeExp = new DateTime($expTok);
        if ($timeNow < $timeExp) {
        //  Show form for inputing new password
            echo '
<p>Upišite e-mail adresu koju koristite za prijavu na ovu stranicu te novu lozinku
koju ćete koristiti za prijavu</p>
<form id="form_rpwd2"  method="post" action="php/rpwd2.php" >
    <table>
        <tr>
            <td>Email</td>
            <td><input required="required" onKeyPress="submitByEnter(event, this.form)" type="email" name="email" value="'.(isset($_COOKIE[$usernameCookie])?getUserDeHashCookie():"").'" placeholder="Vaša e-mail adressa"/></td>
        </tr>
        <tr>
            <td>Promjena lozinke*</td>
            <td>
                <input required="required"  type="password" name="passwd" placeholder="Lozinka" />
                (<span style="font-size: 12px;">Lozinka mora biti duža od 6 znakova!</span>)
            </td>
        </tr>
        <tr>
            <td>Ponovite lozinku*</td>
            <td><input required="required" onKeyPress="submitByEnter(event, this.form)" type="password" name="passwd2" placeholder="Lozinka" /></td>
        </tr>
    </table>
        
	<br/>
	<br/>
	<input type="button" class="button_generic" onclick="submitForm(this.form,'."'".$token."'".')" name="reqPwd" value="Promjeni lozinku"  />
	<br/>
	<div id="form_rpwd2_status">
	</div>
	<br/>
</form>';
        } else {
            //  Request has expired, suggest user generating new one
            echo 'Vaš link za ponovno postavljanje lozinke je istekao, molimo <a href="?page=resetPwd">zatražiti novi link</a>.';
        }
    } else {
        //  Display error message
        echo '
<h3>Dogodila se greška</h3>
<p>Nešto nije u redu s vašim zahtjevom, molimo pokušajte ponovno. <br/>
Ukoliko se problem nastavi molimo kontaktirajte nas na 
<a href="mailto:e.trznica.info@gmail.com">e.trznica.info@gmail.com</a> 
';
    }
}

?>