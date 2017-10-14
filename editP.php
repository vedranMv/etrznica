
<h2>Upravljanje vašim postojećim proizvodima</h2>
<p>Na ovoj stranici možete pregledati svoje postojeće proizvode, obrisati ih ili
izmjeniti informacije o istima.</p>


<h3>Lista proizvoda</h3>
<div id="subcont_prodlist">

<?php 
require_once "php/connFile.php";
require_once "php/sessionManager.php";

// Check for valid session and obtaiun userid
$userid = sessionToUID(getUserCookie(), getSessionCookie());

if ($userid === (-1)){
    //  If invalid user session return here
    return;
}


// Get product information from database for products owned by this user
$stmt = $conHandle->prepare("SELECT id, naziv FROM proizvodi WHERE userid = ?") or die("Error binding");
$stmt->bind_param("i", $userid);
$stmt->execute();

$stmt->bind_result($id, $nazivP);
$count = 0;
while($stmt->fetch())
{
    echo '
    <div id="subcont_p'.$id.'" class="subcont_proizvodi" onclick="fetchPdata('."'".$id."'".',this)">
        '.$nazivP.'
    </div>
';
    $count++;
}

if ($count === 0) {
    echo '
    <div class="subcont_proizvodi" >
        Nemate postavljenih proizvoda, probajte <a href="?page=addP">dodati novi proizvod</a>
    </div>';
}

$stmt->close();

?>

</div>

<div id="subcont_prodinfo">
<form id="form_editproduct"  method="post" action="php/addProd.php" >
<input type="number" name="id" contenteditable="false" hidden="true" value="" />
    <table>
        <tr>
            <td>Naziv<br/>proizvoda*</td>
            <td><input required="required" type="text" name="nazivP" placeholder="Naziv"/></td>
        </tr>
        <tr>
            <td>Opis<br/>proizvoda*</td>
            <td><textarea required="required" name="opisP" rows="6" cols="50" placeholder="Opis proizvoda, dostupna količina, cijena..."></textarea><br/></td>
      </tr>
        <tr>
            <td>Slika</td>
            <td>
            	<input type="file" name="slikaP" placeholder="Lokacija slike" /> 
            	<br/>
            	<span style="font-size: 12px;">Vaša će slika nakon postavljanja biti smanjena na 300 x 300 px</span>
            </td>
        </tr>
    </table> 

	<br/>
	<br/>
	<input type="button" class="button_generic" onclick="submitForm(this.form)" name="dodaj" value="Spremi promjene"  />
	<br/>
    	<div id="form_editproduct_status">
    	</div>
</form>
</div>
