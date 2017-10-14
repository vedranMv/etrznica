<?php
require_once "php/connFile.php";
require_once "php/zupLookup.php";
require_once "php/sessionManager.php";
// Check for valid session and obtaiun userid
$userid = sessionToUID(getUserCookie(), getSessionCookie());

if ($userid === (-1)){
    //  If invalid user session return here
    return;
}
// Get user information from database that user can change
$stmt = $conHandle->prepare("SELECT emailStr, naziv, kontakt, zupanija, mjesto FROM korisnici WHERE id = ?");
$stmt->bind_param("i", $userid);
$stmt->execute();

$stmt->bind_result($emailK, $nazivK, $kontaktK, $zupanijaK, $mjestoK);
$stmt->fetch();

$stmt->close();
?>

<div style="float:left;">
	<h2>Promjena osobnih podataka</h2>
</div>

<form style="float:right;">
	<input type="button" class="button_important" onclick="deleteSw('account', 0)" name="delete" value="Obriši račun"  />
</form>

<div style="clear:both;"></div>


<form id="form_changesettings"  method="post" action="php/updateSettings.php" >

<p>Promijenite samo polja koja sadrže informacije koje želite izmijeniti!</p><br/>
 <table>
  <tr>
    <td>Email za prijavu*</td>
    <td><input required="required" type="email" name="email" value="<?php echo $aesEngine->decrypt(base64_decode($emailK)); ?>" placeholder="Vaša e-mail adressa"/></td>
  </tr>
  <tr>
    <td>Promjena lozinke*</td>
    <td>
        <input type="password" name="passwd" placeholder="Lozinka" />
        (<span style="font-size: 12px;">Lozinka mora biti duža od 6 znakova!</span>)
    </td>
  </tr>
  <tr>
    <td>Ponovite lozinku*</td>
    <td><input type="password" name="passwd2" placeholder="Lozinka" /></td>
  </tr>
</table> 
	 
	  <br/>
	<p>Podaci o vama</p><br/>
	 <table>
      <tr>
        <td>Naziv prodavača*</td>
        <td><input required="required" type="text" name="naziv" value="<?php echo $nazivK; ?>" placeholder="Vaš naziv"/></td>
      </tr>
      <tr>
        <td>Kontakt*</td>
        <td><textarea required="required" name="kontakt" rows="3" cols="60" placeholder="Kontak informacije dostupne pri pregledu vaših proizvoda"><?php echo $kontaktK; ?></textarea><br/></td>
      </tr>
      <tr>
        <td>Županija prodaje*</td>
        <td>
        	<select name="zupanija">
        	<?php 
        	for ($i = 1; $i <= 22; $i++)
        	{
        	    if ($i !== $zupanijaK){
        	        echo '<option value="'.$i.'">'.$zupanije[$i].'</option>\n';
        	    } else {
        	        echo '<option value="'.$i.'" selected="selected">'.$zupanije[$i].'</option>';
        	    }
        	}
        	?>
        	</select>
		</td>
      </tr>
      <tr>
        <td>Mjesto prodaje*</td>
        <td><input type="text" name="mjesto" value="<?php echo $mjestoK; ?>" placeholder="Mjesto"/></td>
      </tr>
    </table> 

	<br/>
	<br/>
	<input type="button" class="button_generic" onclick="submitForm(this.form)" name="update" value="Spremi promjene"  />
	<br/>
    	<div id="form_changesettings_status">
    
    	</div>
	<br/>
</form>


