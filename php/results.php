<?php 
/**
 * Print a result page for a selected product
 */
require_once "connFile.php";
require_once "zupLookup.php";
    
$pid = 0;
$uid = 0;

//  Chekc for incoming POST arguments
if (isset($_POST['query']))
    $pid = $_POST['query'];

if (isset($_POST['user']))
    $uid = $_POST['user'];


//  If we've received a valid query search for it in database
if (($pid != 0) && ($uid != 0))
{
    // Get product information from database
    $stmt = $conHandle->prepare("SELECT naziv, opis, slika FROM proizvodi WHERE id = ?") or die("Error binding");
    $stmt->bind_param("i", $pid);
    $stmt->execute();
    
    $stmt->bind_result($nazivP, $opisP, $slikaK);
    $stmt->fetch();
    
    $stmt->close();
    
    // Get information for owner of the product
    $stmt = $conHandle->prepare("SELECT naziv, kontakt, zupanija, mjesto FROM korisnici WHERE id = ?") or die("Error binding");
    $stmt->bind_param("i", $uid);
    $stmt->execute();
    
    $stmt->bind_result($nazivK, $kontaktK, $zupanijaK, $mjestoK);
    $stmt->fetch();
    
    $stmt->close();
}
//class="tt_user"
?>

    <h2>  <?php echo $nazivP; ?> </h2>
    
    <h4 style="margin-bottom:0px;">Prodavač: <?php echo '<a class="tt_user link_dash" href="?page=ponuda&filter=osoba&query='.$nazivK.'" >'.$nazivK.'<span class="tt_cont">Pokaži sve prozvode ovog korisnika</span></a>'; ?> </h4>
    <p style="margin-top:0px;">Prodavač se nalazi u okolica mjesta <?php echo ($mjestoK === "")?("Nije dostupno"):$mjestoK; ?>, županija <?php echo '<a class="tt_user link_dash" href="?page=ponuda&filter=zupanija&query='.$zupanijaK.'">'.$zupanije[$zupanijaK].'<span class="tt_cont">Pokaži sve prozvode u ovoj županiji</span></a>'; ?> </p>
    <img class="proizvod_slika" src="<?php echo $slikaK; ?>">
	<div class="proizvod_opis">
		<?php echo $opisP; ?>
	</div>
    <div style="clear:both;"></div>
    
    <div id="subcont_kontakt">
    	<p style="margin-left:0px;">Dostupne kontakt informacije: </p>
    	<p> <?php echo $kontaktK; ?> </p>
    </div>
    
    <form style="float:right;margin-top:5px;">
    	<input type="button" class="button_generic" onclick="<?php echo "fileReport('proizvod',".$pid.");"; ?>" name="report" value="Prijavi neprikladan sadržaj"  />
	</form>
	
	<div style="clear:both;"></div>


    
    