<?php
//  Print a result page for a selected product
require_once "connFile.php";
require_once "sessionManager.php";


$pid = (-1);
//  Check for incoming POST arguments
if (isset($_POST['pid']))  {
    $pid = $_POST['pid'];
}

//Initialize variables
$errorMsg = "";
$ret = TRUE;

if ($pid !== (-1))
{
    //First check for valid session
    $userid = sessionToUID(getUserCookie(), getSessionCookie());
    
    if ($userid === (-1))
    {
        $errorMsg .= "Dogodila se greška, molimo kontaktirajte administratora stranice";
        echo $errorMsg;
        return;
    }
    
    //Check if product belongs to this user
    $stmt = $conHandle->prepare("SELECT userid, slika FROM proizvodi WHERE id = ?") or die("1Error binding");
    $stmt->bind_param("i", $pid);
    $ret &= $stmt->execute();
    
    $stmt->bind_result($useridP, $slikaP);
    $stmt->fetch();
    
    
    if ($userid !== $useridP)
    {
        echo $pid."\n".$userid."\n".$slikaP;
        $errorMsg .= "Proizvod koji pokušavate obrisati ne pripada vama!";
        echo $errorMsg;
        return;
    }
    $stmt->close();
    
    //Delete picture of this product from disk
    if (file_exists('../'.$slikaP))
    {
        unlink('../'.$slikaP);
    }
    
    //Delete product entry from database
    $stmt = $conHandle->prepare("DELETE FROM proizvodi WHERE id = ?") or die("2Error binding");
    $stmt->bind_param("i", $pid);
    $ret &= $stmt->execute();
    $stmt->close();
    
    if ($ret)
    {
        $errorMsg .= "Proizvod je uspješno uklonjen";
    } else {
        $errorMsg .= "Dogodila se greška priliko uklanjanja proizvoda, molimo kontaktirajte administratora strance";
    }
    
}

echo $errorMsg;

?>