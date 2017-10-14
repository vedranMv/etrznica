<?php 
/**
 * Update information about existing product
 * This script receives input from HTML form through POST. It then authenticates
 * user based on cookie data and validates POST data content. If both checks are
 * passed, DB is updated with new product data.
 */
require_once "connFile.php";
require_once "zupLookup.php";
require_once "sessionManager.php";

//  Fetch all data through POST
$id = -1;
if (isset($_POST['id'])) {
    $id = $_POST['id'];
}

$nazivP = "";
//  Chekc for incoming POST arguments
if (isset($_POST['nazivP'])){
    $nazivP = ucfirst($_POST['nazivP']);
}

$opisP = "";
if (isset($_POST['opisP'])) {
    $opisP = ucfirst($_POST['opisP']);
}
//  Initialize variables for verifying data type of uploaded picture
$valid_file_extensions = array();
$file_extension = "";

if (isset($_FILES['slikaP'])) {
    $img = $_FILES['slikaP'];
    //  Permit only pictures of these formats
    $valid_file_extensions = array(".jpg", ".jpeg", ".gif", ".png");
    //  Get extension of a file user uploaded
    $file_extension = strrchr($img["name"], ".");
}

//  This shouldn't happen, but anyway, make sure we have id for updating data
if ($id === (-1)){
    echo "Dogodila se greška prilikom slanja zahtjeva, molimo pokušajte ponovo.";
    return;
}

$errorMsg = "";

//  First check for valid session
$userid = sessionToUID(getUserCookie(), getSessionCookie());

if ($userid === (-1))
{
    $errorMsg .= "Dogodila se greška, molimo kontaktirajte administratora stranice";
    echo $errorMsg;
    return;
}

//  Full path to the uploaded image once it's saved in the image folder
$destination = "";
//  Relative path to the uploaded image from the index.php file, this path gets
//  written to DB in order to load image on request
$savedTo = "";

$uploaded = FALSE;
// Check that the extension of uploaded file matches the extensions we defined
//  in $valid_file_extensions array 
if (in_array($file_extension, $valid_file_extensions))
{
    //  Double check for image, this should block scripts disguised as images
    //  (script files with image extension)
    if (getimagesize($img["tmp_name"]) !== FALSE) {
        //  Instead of user-provided image name, images are named with the
        //  hash of current time and are all saved as .pngs
        $fName = md5(time());
        $fName .= '.png';
        //  Adjust location from where the image should be fetched from by other
        //  HTML files (note that HTML files reside in a parent folder of this
        //  script so the ../ is not present
        $savedTo = "imgs/uploads/".$fName;
        //  Adjust location for where to save image relative to this script
        $destination = "../".$savedTo;
        
        //  Resize image to fit within 300x300px
        //Set max dimension of an image
        $maxDim = 300;
        $file_name = $img["tmp_name"];
        
        list($width, $height, $type, $attr) = getimagesize($img["tmp_name"]);
        //  Generate new dimensions for oversized images by keeping aspect ratio
        if ( $width > $maxDim || $height > $maxDim ) {
            $ratio = $width/$height;
            if( $ratio > 1) {
                $new_width = $maxDim;
                $new_height = $maxDim/$ratio;
            } else {
                $new_width = $maxDim*$ratio;
                $new_height = $maxDim;
            }
            //  Create temporary image from uploaded file
            $src = imagecreatefromstring(file_get_contents($img["tmp_name"]));
            //  Create empty image with new, adjusted dimensons
            $dst = imagecreatetruecolor($new_width, $new_height);
            //  Copy uploaded image into a resized one
            imagecopyresampled($dst, $src, 0, 0, 0, 0, $new_width, $new_height, $width, $height );
            imagedestroy($src);
            //  Save resized image and clear memory occupied by it
            if (imagepng($dst, $destination, 9)) {
                $errorMsg .= "Slika uspješno spremljena <br/>";
                $uploaded = TRUE;
            }
            else {
                $errorMsg .= "Greška pri spremanju slike <br/>";
            }
            imagedestroy($dst);            
        } else {
            //  If no resizing is needed save image directly
            if (move_uploaded_file($img["tmp_name"], $destination)){
                $errorMsg .="Slika uspiješno spremljena <br/>";
                $uploaded = TRUE;
            }
            else {
                $errorMsg .="Greška pri spremanju slike <br/>";
                $uploaded = FALSE;
            }
        }
        
        if ($uploaded){            
            //  If new image is uploaded delete old one from disk and DB
            $stmt = $conHandle->prepare("SELECT slika FROM proizvodi WHERE (id = ?) AND (userid = ?)") or die("Error binding");
            $stmt->bind_param("ii", $id, $userid);
            $stmt->execute();
            
            $stmt->bind_result($slikaP);
            $stmt->fetch();
            $stmt->close();
            
            //Delete picture of this product from disk
            if (file_exists('../'.$slikaP)) {
                unlink('../'.$slikaP);
            }
        }
        else {
            $errorMsg .="Greška pri spremanju slike <br/>";
        }
    } else {
        $errorMsg .="Pogrešan format slike<br/>";
    }
}
else {
    $errorMsg .="Slika nije zaprimljena<br/>";
}

//  Validate input parameters
if (!(isEmptyStr($nazivP) || isEmptyStr($opisP)))
{
    //  Change query depending on whether or not we're updating picture as well
    if ($savedTo !== "") {
        $stmt = $conHandle->prepare("UPDATE proizvodi SET naziv = ?, opis = ?, slika = ?  WHERE (id = ?) AND (userid = ?)") or die("Error binding");
        $stmt->bind_param("sssii", $nazivP, $opisP, $savedTo, $id, $userid);
    }else {
        $stmt = $conHandle->prepare("UPDATE proizvodi SET naziv = ?, opis = ?  WHERE (id = ?) AND (userid = ?)") or die("Error binding");
        $stmt->bind_param("ssii", $nazivP, $opisP, $id, $userid);
    }
    
    //  Update product info in database
    $ret = $stmt->execute();
    if ($ret) {
        $errorMsg .= "Promjene uspješno spremljene";
    } else {
        $errorMsg .= "Dogodila se greška pri spremanju promjena, molimo pokušajte ponovo.";
    }
    $stmt->close();
}

echo $errorMsg;

?>