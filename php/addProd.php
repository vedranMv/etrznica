<?php 
/**
 * Add product for a specific user
 * This script receives input from HTML form through POST. It then authenticates
 * user based on cookie data and validates POST data content. If both checks are
 * passed, new product is inserted into DB.
 */
require_once "connFile.php";
require_once "zupLookup.php";
require_once "sessionManager.php";


//  Fetch all data through POST
$nazivP = "";
//  Chekc for incoming POST arguments
if (isset($_POST['nazivP'])){
    $nazivP = $_POST['nazivP'];
}

$opisP = "";
if (isset($_POST['opisP'])) {
    $opisP = $_POST['opisP'];
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

$errorMsg = "";

//  First check for valid session
$userid = sessionToUID(getUserCookie(), getSessionCookie());
if ($userid === (-1))
{
    $errorMsg .= "Dogodila se greška s vašom trenutnom sesijom, molimo prijavite se ponovo";
    echo $errorMsg;
    return;
}


//  Full path to the uploaded image once it's saved in the image folder
$destination = "";
//  Relative path to the uploaded image from the index.php file, this path gets
//  written to DB in order to load image on request
$savedTo = "";

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
            }
            else {
                $errorMsg .= "Greška pri spremanju slike <br/>";
            }
            imagedestroy($dst);
        } else {
            //  If no resizing is needed save image directly
            if (move_uploaded_file($img["tmp_name"], $destination)){
                $errorMsg .= "Slika uspješno spremljena <br/>";
            }
            else {
                $errorMsg .= "Greška pri spremanju slike <br/>";
            }
        }
    }
    else {
        $errorMsg .= "Pogrešan format slike<br/>";
    }
} else {
    $errorMsg .= "Slika nije zaprimljena ili je pogrešnog formata<br/>";
}

//  Enter changes into database if the required fields have been populated
if (!(isEmptyStr($nazivP) || isEmptyStr($opisP)))
{   
    // Insert new product into DB
    $stmt = $conHandle->prepare("INSERT INTO proizvodi(userid, naziv, opis, slika) VALUES (?, ?, ?, ?)") or die ("Error binding");
    $stmt->bind_param("isss",$userid, $nazivP, $opisP, $savedTo);
    $ret = $stmt->execute();
    
    //  Check outcome of insert query
    if ($ret === TRUE) {
        $errorMsg .= "Vaš proizvod je uspješno dodan u sustav!<br/>";
    } else {
        $errorMsg .= "Dogodila se greška pri dodavanju proizvoda, molimo pokušajte ponovno.<br/>";
    }
    
    $stmt->close();
}

echo $errorMsg;

?>

