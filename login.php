<?php
global $usernameCookie;
?>
<h2>Unesite svoje podatke za prijavu</h2>
<form id="form_login"  method="post" action="php/loginScript.php" >
    <table>
    <tr>
        <td>Email za prijavu</td>
        <td><input required="required" onKeyPress="submitByEnter(event, this.form)" type="email" name="email" value="<?php if (isset($_COOKIE[$usernameCookie])) echo getUserDeHashCookie(); ?>" placeholder="Vaša e-mail adressa"/></td>
    </tr>
    <tr>
        <td>Lozinka za prijavu</td>
        <td><input required="required" onKeyPress="submitByEnter(event, this.form)" type="password" name="passwd" placeholder="Lozinka" /></td>
    </tr>
</table> 

	<br/>
	<br/>
	<input type="button" class="button_generic" onclick="submitForm(this.form)" name="login" value="Prijava"  />
	<br/>
	<div id="form_login_status">
	</div>
	<br/>
	<a href="?page=resetPwd">Zaboravili ste lozinku?</a>

</form>