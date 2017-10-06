<?php 
//  Print a result page for a selected product
require_once "connFile.php";
require_once "zupLookup.php";
require_once "sessionManager.php";


//  Chekc for incoming POST arguments
if (isset($_POST['id'])){
    $id = $_POST['id'];
}

// Check for valid session and obtaiun userid
$userid = sessionToUID(getUserCookie(), getSessionCookie());

if ($userid === (-1)){
    //  If invalid user session return here
    return;
}


// Get product information from database for products owned by this user
$stmt = $conHandle->prepare("SELECT naziv, opis, slika FROM proizvodi WHERE (id = ?)") or die("Error binding");
$stmt->bind_param("i", $id);
$stmt->execute();

$stmt->bind_result($nazivP, $opisP, $slikaP);
$stmt->fetch();


echo '
<form id="form_editp"  method="post" action="php/updateProd.php" >

    <table>
        <tr>
            <td>Naziv proizvoda*</td>
            <td><input required="required" type="text" name="nazivP" placeholder="Naziv" value="'.$nazivP.'"/></td>
        </tr>
        <tr>
            <td>Opis proizvoda*</td>
            <td><textarea required="required" name="opisP" rows="8" cols="60" placeholder="Opis proizvoda...">'.$opisP.'</textarea><br/></td>
      </tr>
        <tr>
            <td>Slika
            </td>
            <td>
                <img class="img_small" alt="" src="'.$slikaP.'"/>
                <input type="file" name="slikaP" placeholder="Lokacija slike" size="1024" />
            </td>
        </tr>
    </table> 

	<br/>
	<br/>
	<input type="button" onclick="submitForm(this.form, '.$id.')" name="dodaj" value="Spremi"  />
	<br/>
    	<div id="form_editp_status">
    	</div>
</form>
<form style="float:right;margin-top:-25px;">
    <input type="button" class="button_important" onclick="deleteSw('."'product'".', '.$id.')" name="delete" value="Ukloni proizvod"  />
</form>

<div style="clear:both;"></div>';


$stmt->close();

?>