<?php 
/**
 * Generate form for updating product info based on form id
 */
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
$stmt = $conHandle->prepare("SELECT naziv, opis, slika FROM proizvodi WHERE (id = ?) AND (userid = ?)") or die("Error binding");
$stmt->bind_param("ii", $id, $userid);
$stmt->execute();

$stmt->bind_result($nazivP, $opisP, $slikaP);
$stmt->fetch();


echo '
<form style="float:right;margin-top:25px;margin-right:25px;">
    <input type="button" class="button_important" onclick="deleteSw('."'product'".', '.$id.')" name="delete" value="Ukloni proizvod"  />
</form>
<form id="form_editp"  method="post" action="php/updateProd.php" >

    <table>
        <tr>
            <td>Naziv<br/>proizvoda*</td>
            <td><input required="required" type="text" name="nazivP" placeholder="Naziv" value="'.$nazivP.'"/></td>
        </tr>
        <tr>
            <td>Opis<br/>proizvoda*</td>
            <td><textarea required="required" name="opisP" rows="8" cols="55" placeholder="Opis proizvoda...">'.$opisP.'</textarea><br/></td>
      </tr>
        <tr>
            <td>Slika
            </td>
            <td>
                <img class="img_small" alt="" src="'.$slikaP.'"/><br/>
                <input type="file" name="slikaP" placeholder="Lokacija slike" />
            	<br/>
            	<span style="font-size: 12px;">Dozvoljen format slika: .jpg, .jpeg, .png, .gif</span> <br/>
            	<span style="font-size: 12px;">Vaša će slika nakon postavljanja biti smanjena na 300 x 300 px</span>
            </td>
        </tr>
    </table> 

	<br/>
	<br/>
	<input type="button" class="button_generic" onclick="submitForm(this.form, '.$id.')" name="dodaj" value="Spremi promjene"  />
	<br/>
    	<div id="form_editp_status">
    	</div>
</form>

<div style="clear:both;"></div>';


$stmt->close();

?>