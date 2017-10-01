<h2>Upravnjanje vašim postojećim proizvodima</h2>
<p>Na ovoj stranici možete pregledati svoje postojeće proizvode, obrisati ih ili
izmjeniti informacije o istima.</p>

<h3>Lista proizvoda</h3>
<div id="subcont_prodlist">

<?php 
include "php/connFile.php";

// Get product information from database
$stmt = $conHandle->prepare("SELECT id, naziv, opis, slika FROM proizvodi") or die("Error binding");
$stmt->execute();

$stmt->bind_result($id, $nazivP, $opisP, $slikaP);
while($stmt->fetch())
{
    echo '
    <div class="subcont_proizvodi" onclick="fetchPdata('.$id.','."'".$nazivP."'".','."'".$opisP."'".','."'".$slikaP."'".')">
        '.$nazivP.'
    </div>
';
}

$stmt->close();

?>

</div>

<div id="subcont_prodinfo">
<form id="form_editproduct"  method="post" action="php/addProd.php" >
<input type="number" name="id" contenteditable="false" hidden="true" value="" />
    <table>
        <tr>
            <td>Naziv proizvoda*</td>
            <td><input required="required" type="text" name="nazivP" placeholder="Naziv"/></td>
        </tr>
        <tr>
            <td>Opis proizvoda*</td>
            <td><textarea required="required" name="opisP" rows="6" cols="50" placeholder="Opis proizvoda..."></textarea><br/></td>
      </tr>
        <tr>
            <td>Slika</td>
            <td><input type="file" name="slikaP" placeholder="Lokacija slike" size="1024" /></td>
        </tr>
    </table> 

	<br/>
	<br/>
	<input type="button" onclick="submitForm(this.form)" name="dodaj" value="Spremi"  />
	<br/>
    	<div id="form_editproduct_status">
    	</div>
</form>
</div>
