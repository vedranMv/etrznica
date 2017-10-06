<?php 

    //  Registration of new users to the system
    include "connFile.php";
    include "sessionManager.php";
    
    
    //  Fetch all data through POST
    if (isset($_POST['email']))
        $email = $_POST['email'];
    else
        $email = "";
            
    if (isset($_POST['passwd']))
        $passwd = $_POST['passwd'];
    else
        $passwd = "";
    
    $errorMsg = "";
    //  If no arguments skip writing to DB
    if (($email !== "") && ($passwd !== ""))
    {
        $email = $aesEngine->encrypt($email);
        $email = base64_encode($email);
        //  Find user in database
        $stmt = $conHandle->prepare("SELECT passwordStr FROM korisnici WHERE emailStr = ?") or die("Error binding");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        
        $stmt->bind_result($passK);
        $stmt->store_result();
        
        //  Save username in cookies, cookie expires after 1 day
        setcookie($usernameCookie,$email,(time()+24*60*60), '/');
        $exists = ($stmt->num_rows() > 0);
        
        $stmt->fetch();
        
        //  User doesn't exist
        if (!$exists) 
        {
            $errorMsg = 'Neispravna kombinacija korisničkog imena i lozinke, pokušajte ponovo.';
        }
        
        //  Verify password
        if (password_verify($passwd, $passK)) 
        {
            $errorMsg = 'Prijava uspiješna, nakon preusmjerenja možete početi s korištenjem stranice.';
            createNewSessionFor($email);

        } else {
            $errorMsg = 'Neispravna kombinacija korisničkog imena i lozinke, pokušajte ponovo.';
        }
        
    }
    else
        $errorMsg = "Neispravna kombinacija korisničkog imena i lozinke, pokušajte ponovo.";
    
    echo $errorMsg;
?>

