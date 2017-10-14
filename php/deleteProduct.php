<?php
/**
 * Delete product from database
 * This script receives a request from HTML form to delete a product with 
 * specific id from DB. Script first validates user's session and confirms that
 * the product belongs to the user. If both checks are passed it proceeds with 
 * removing the product from DB.
 */
require_once "connFile.php";
require_once "sessionManager.php";


$pid = (-1);
//  Check for incoming POST arguments
if (isset($_POST['pid']))  {
    $pid = $_POST['pid'];
}

//  Initialize variables
$errorMsg = "";
$ret = TRUE;

//  Verify that we've received a valid product id
if ($pid !== (-1))
{
    //  Check for a valid session
    $userid = sessionToUID(getUserCookie(), getSessionCookie());
    if ($userid === (-1)) {
        echo "Dogodila se greška, molimo kontaktirajte administratora stranice";
        return;
    }
    
    //  Check if product belongs to this user
    $stmt = $conHandle->prepare("SELECT userid, slika FROM proizvodi WHERE id = ?") or die("1Error binding");
    $stmt->bind_param("i", $pid);
    $ret &= $stmt->execute();
    
    $stmt->bind_result($useridP, $slikaP);
    $stmt->fetch();
    
    //  Perform validation based on query result
    if ($userid !== $useridP) {
        $errorMsg .= "Proizvod koji pokušavate obrisati ne pripada vama!";
        echo $errorMsg;
        return;
    }
    $stmt->close();
    
    //  Delete picture of this product from disk
    if (file_exists('../'.$slikaP)) {
        unlink('../'.$slikaP);
    }
    
    //  Delete product entry from database
    $stmt = $conHandle->prepare("DELETE FROM proizvodi WHERE (id = ?) AND (userid = ?)") or die("2Error binding");
    $stmt->bind_param("ii", $pid, $userid);
    $ret &= $stmt->execute();
    $stmt->close();
    
    if ($ret) {
        $errorMsg .= "Proizvod je uspješno uklonjen";
    } else {
        $errorMsg .= "Dogodila se greška priliko uklanjanja proizvoda, molimo kontaktirajte administratora strance";
    }
}

echo $errorMsg;

?>