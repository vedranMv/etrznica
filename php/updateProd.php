<?php 
require_once "connFile.php";
require_once "zupLookup.php";
require_once "sessionManager.php";

$id = -1;
if (isset($_POST['id'])) {
    $id = $_POST['id'];
}

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

//  This shouldn't happen, but anyway, make sure we have id for updating data
if ($id === (-1)){
    return;
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
    $uid = sessionToUID(getUserCookie(), getSessionCookie());
    //  Return here is session is not correct
    if ($uid === (-1))
    {
        echo "Dogodila se greška s vašom trenutnom sesijom, molimo prijavite se ponovo";
        return;
    }
    else {
        if ($savedTo !== "") {
            $stmt = $conHandle->prepare("UPDATE proizvodi SET naziv = ?, opis = ?, slika = ?  WHERE (id = ?)") or die("Error binding");
            $stmt->bind_param("sssi", $nazivP, $opisP, $savedTo, $id);
        }else {
            $stmt = $conHandle->prepare("UPDATE proizvodi SET naziv = ?, opis = ?  WHERE (id = ?)") or die("Error binding");
            $stmt->bind_param("ssi", $nazivP, $opisP, $id);
        }
        
        // Update product info in database
        $stmt->execute();
        echo "Promjene spremljene";
        $stmt->close();
    }
}

?>