<?php 

    //  Registration of new users to the system
    include "connFile.php";
    include "zupLookup.php";
   

    //  Fetch all data through POST
    if (isset($_POST['email']))
        $email = $_POST['email'];
    else
        $email = "";
            
    if (isset($_POST['passwd']))
        $passwd = $_POST['passwd'];
    else
        $passwd = "";
            
    if (isset($_POST['naziv']))
        $naziv= $_POST['naziv'];
    
    if (isset($_POST['kontakt']))
        $kontakt = $_POST['kontakt'];
        
    if (isset($_POST['zupanija']))
        $zup = $_POST['zupanija'];
    else
        $zup = 22;
            
    if (isset($_POST['mjesto']))
        $mjesto = $_POST['mjesto'];
    else
        $mjesto = " ";

    //  If no arguments skip writing to DB
    if (($email !== "") && ($passwd !== ""))
    {
        //  Check if there's already a user with this email
        // Get product information from database
        $stmt = $conHandle->prepare("SELECT emailStr,naziv FROM korisnici WHERE emailStr = ?") or die("rror binding");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        
        $stmt->bind_result($emailK, $nazivK);
        
        $exists = false;
        while($stmt->fetch())
        {
            $exists = true;
            break;
        }
        
        $errorMsg = "Korisnik s navedenom e-mail addresom već postoji, ukoliko ste zaboravili svoju lozinku probajte istu resetirati.";
        
        $stmt->close();
        //  Stop here if we already have this email
        if ($exists) 
        {   
            echo $errorMsg;
            return;
        }
        
        
        /* Prepared statement, stage 1: prepare */
        if (!($stmt = $conHandle->prepare("INSERT INTO korisnici(emailStr, passwordStr, saltStr, naziv, kontakt, datumReg, zupanija, mjesto, zupanijaStr) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"))) {
            echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
        }
        
        $options = [
            'cost' => 11,
            'salt' => mcrypt_create_iv(22, MCRYPT_DEV_URANDOM),
        ];
        // set parameters and execute
        $emP    = $email;
        $pwdP   = password_hash($passwd, PASSWORD_BCRYPT, $options);
        $saltP  = $options['salt'];
        $nazP   = $naziv;
        $koP    = $kontakt;
        $datP   = date("d-m-Y");
        $zupP   = $zup;
        $mjeP   = $mjesto;
        $ppdi   = -1;
        
        
        if (!$stmt->bind_param("ssssssiss", $emP, $pwdP, $saltP, $nazP, $koP, $datP, $zupP, $mjeP, $zupanije[$zupP] )) {
            echo "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
        }
        $ret = $stmt->execute();
        
        if ($ret === TRUE)
        {
            //  Save username in cookies, cookie expires after 1 day
            setcookie("mojDucan_username",$email,(time()+24*60*60));
            $errorMsg = "Uspješno ste se registrirali. Sada se možete prijaviti u sustav";
        }
        else
        {
            $errorMsg = "Dogodila se greška kod registracije";
            echo "Exec failed: (" . $stmt->errno . ") " . $stmt->error;
        }
        
        $stmt->close();
    }
    else
        $errorMsg = "Molimo ispravno popunite sva obavezna polja označena *";
    
    echo $errorMsg;
?>


