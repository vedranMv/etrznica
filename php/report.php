<?php 
/**
 * Report inappropriate product for evaluation
 */
require_once "connFile.php";
require_once "sessionManager.php";

// Function to get the client IP address
function get_client_ip() {
    $ipaddress = '';
    if (getenv('HTTP_CLIENT_IP'))
        $ipaddress = getenv('HTTP_CLIENT_IP');
    else if(getenv('HTTP_X_FORWARDED_FOR'))
        $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
    else if(getenv('HTTP_X_FORWARDED'))
        $ipaddress = getenv('HTTP_X_FORWARDED');
    else if(getenv('HTTP_FORWARDED_FOR'))
        $ipaddress = getenv('HTTP_FORWARDED_FOR');
    else if(getenv('HTTP_FORWARDED'))
        $ipaddress = getenv('HTTP_FORWARDED');
    else if(getenv('REMOTE_ADDR'))
        $ipaddress = getenv('REMOTE_ADDR');
    else
        $ipaddress = 'UNKNOWN';
    return $ipaddress;
}

$report = "";
if (isset($_POST['report']))  {
    $report = $_POST['report'];
}

$rid = -1;
if (isset($_POST['id']))  {
    $rid = $_POST['id'];
}


$errorMsg = "";

if (($report === "proizvod") && ($rid !== (-1)))
{
    //Get IP of machine filing a report and a date of report
    $ipP = get_client_ip();
    $datP   = date("H:i:sO, d-m-Y");
    
    //Insert data into appropriate table in DB
    $stmt = $conHandle->prepare("INSERT INTO reports(productid, ipReport, date) VALUES (?, ?, ?)");
    //  Bind parameters for prepared statement
    $stmt->bind_param("iss", $rid, $ipP, $datP);
    $ret = $stmt->execute();
    
    if ($ret === TRUE) {
        $errorMsg .= "Prijava je zaprimljena \nAdministratori će razmotriti vašu prijavu u što kraćem roku";
    } else {
        $errorMsg .="Dogodila se greška prilikom prijave, molimo pokušajte ponovo";
    }
}

echo $errorMsg;

?>