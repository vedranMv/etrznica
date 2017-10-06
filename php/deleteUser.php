<?php 
//  Print a result page for a selected product
require_once "connFile.php";
require_once "sessionManager.php";


$delete = FALSE;
//  Check for incoming POST arguments
if (isset($_POST['delete']))  {
    $delete = $_POST['delete'];
}

//Initialize variables
$errorMsg = "";
$ret = TRUE;

if ($delete)
{
    //First check for valid session
    $userid = sessionToUID(getUserCookie(), getSessionCookie());
    
    if ($userid === (-1))
    {
        $errorMsg .= "Dogodila se greška, molimo kontaktirajte administratora stranice";
        echo $errorMsg;
        return;
    }
    
    //Find all products owned by this user and delete stored pictures
    $stmt = $conHandle->prepare("SELECT slika FROM proizvodi WHERE userid = ?") or die("1Error binding");
    $stmt->bind_param("i", $userid);
    $ret &= $stmt->execute();
    
    $stmt->bind_result($slikaK);
    while ($stmt->fetch())
    {
        if (file_exists('../'.$slikaK))
        {
            unlink('../'.$slikaK);
        }
    }
    
    $stmt->close();
    
    //Delete all products owned by this user
    $stmt = $conHandle->prepare("DELETE FROM proizvodi WHERE userid = ?") or die("2Error binding");
    $stmt->bind_param("i", $userid);
    $ret &= $stmt->execute();   
    $stmt->close();
    
    //Perform deauthentication
    destroyCurrentSession();
    
    //Delete user from database
    $stmt = $conHandle->prepare("DELETE FROM korisnici WHERE id = ?") or die("3Error binding");
    $stmt->bind_param("i", $userid);
    $ret &= $stmt->execute();
    $stmt->close();
    
    if ($ret)
    {
        $errorMsg .= "Vaš je račun uspješno obrisan";
    } else {
        $errorMsg .= "Dogodila se greška priliko brisanja računa, molimo kontaktirajte administratora strance";
    }
}

echo $errorMsg;

?>