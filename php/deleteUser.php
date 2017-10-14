<?php 
/**
 * Delete user account and all of its content from DB
 * This script receives a request from HTML form to delete a specific user from
 * DB. Script first validates user's session and uses same session data to 
 * identify which user needs to be deleted. Upon successful authorization all
 * user's products are deleted following a deletion of user account.
 */
require_once "connFile.php";
require_once "sessionManager.php";


//  Fetch all data through POST
$delete = FALSE;
//  Check for incoming POST arguments
if (isset($_POST['delete']))  {
    $delete = $_POST['delete'];
}

//  Initialize variables
$errorMsg = "";
$ret = TRUE;

//  Dummy check
if ($delete)
{
    //  Check for valid session
    $userid = sessionToUID(getUserCookie(), getSessionCookie());  
    if ($userid === (-1)) {
        echo "Dogodila se greška, molimo kontaktirajte administratora stranice";
        return;
    }
    
    //  Find all products owned by this user and delete stored pictures
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
    
    //  Now delete all products owned by this user
    $stmt = $conHandle->prepare("DELETE FROM proizvodi WHERE userid = ?") or die("2Error binding");
    $stmt->bind_param("i", $userid);
    $ret &= $stmt->execute();   
    $stmt->close();
    
    //  Perform deauthentication
    destroyCurrentSession();
    
    //  Delete username cookie as well
    setcookie($usernameCookie, "", (time()-24*60*60),'/');
    
    
    //  Delete user from database
    $stmt = $conHandle->prepare("DELETE FROM korisnici WHERE id = ?") or die("3Error binding");
    $stmt->bind_param("i", $userid);
    $ret &= $stmt->execute();
    $stmt->close();
    
    if ($ret) {
        $errorMsg .= "Vaš je račun uspješno obrisan";
    } else {
        $errorMsg .= "Dogodila se greška priliko brisanja računa, molimo kontaktirajte administratora strance";
    }
}

echo $errorMsg;

?>