<?php 
//  Print a result page for a selected product
include "connFile.php";
include "zupLookup.php";
//include "confirmSession.php"

$nazivP = "";
//  Chekc for incoming POST arguments
if (isset($_POST['nazivP'])){
    $nazivP = $_POST['nazivP'];
}

$opisP = "";
if (isset($_POST['opisP'])) {
    $opisP = $_POST['opisP'];
}

$valid_file_extensions = array();
$file_extension = "";

if (isset($_FILES['slikaP'])) {
    $img = $_FILES['slikaP'];
    
    $valid_file_extensions = array(".jpg", ".jpeg", ".gif", ".png");
    $file_extension = strrchr($img["name"], ".");
}

$errorMsg = "";
$destination = "";
// Check that the uploaded file is actually an image
// and move it to the right folder if is.
$savedTo = "";
if (in_array($file_extension, $valid_file_extensions)) 
{
    //  Double chekc for image, this prevents disguised images
    if (getimagesize($img["tmp_name"]) !== false) {
        //  Generate random file name 
        $fName = md5(time());
        $destination = '../imgs/uploads/'.$fName . $file_extension;
        $savedTo = "imgs/uploads/".$fName . $file_extension;
        if (move_uploaded_file($img["tmp_name"], $destination)){
            $errorMsg .="Slika uspiješno spremljena <br/>";
        }
        else
        {
            $errorMsg .="Greška pri spremanju slike <br/>";
        }
    }
    else
        $errorMsg .="Pogrešan format slike<br/>";
}
else {
    $errorMsg .="Slika nije zaprimljena<br/>";
}

if (($nazivP !== "") && ($opisP !== ""))
{
    //  Get user ID based on current session (retreived from cookie)
    $stmt = $conHandle->prepare("SELECT id FROM korisnici WHERE session = ?") or die("Error binding");
    $stmt->bind_param("s", $_COOKIE['mojDucan_session']);
    $stmt->execute();
    
    $stmt->bind_result($userid);
    
    $exists = false;
    while($stmt->fetch())
    {
        $exists = true;
        break;
    }
    $stmt->close();
    
    //  Return here is session is not correct
    if (!$exists)
    {
        echo "Dogodila se greška s vašom trenutnom sesijom, molimo prijavite se ponovo";
        return;
    }
// $userid = checkSession($_COOKIE['mojDucan_username'], $_COOKIE['mojDucan_session']);
// if ($userid === (-1))
// {
//     echo "Wrong session";
//     return;
// }
    
    // Prepar insert statement
    $stmt = $conHandle->prepare("INSERT INTO proizvodi(userid, naziv, opis, slika) VALUES (?, ?, ?, ?)") or die ("Error binding");
    
    
    if (!$stmt->bind_param("isss",$userid, $nazivP, $opisP, $savedTo)) {
        echo "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
    }
    $ret = $stmt->execute();
    
    if ($ret === TRUE)
    {
        $errorMsg = "Vaš proizod je uspješno dodan u sustav!";
    }
    else
    {
        $errorMsg = "Dogodila se greška pri dodavanju proizvoda, molimo pokušajte ponovno.";
        echo "Exec failed: (" . $stmt->errno . ") " . $stmt->error;
    }
    
    $stmt->close();
}

echo $errorMsg;

?>

