<?php 

    //  Registration of new users to the system
    include "connFile.php";
    
    
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
        //  Find user in database
        $stmt = $conHandle->prepare("SELECT passwordStr FROM korisnici WHERE emailStr = ?") or die("Error binding");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        
        $stmt->bind_result($passK);
        $exists = false;
        while($stmt->fetch())
        {
            $exists = true;
        }
        //  User doesn't exist
        if (!$exists) 
        {
            $errorMsg = 'Neispravna kombinacija korisničkog imena i lozinke, pokušajte ponovo.';
        }
        
        //  Verify password
        if (password_verify($passwd, $passK)) 
        {
            $errorMsg = 'Prijava uspiješna, nakon preusmjerenja možete početi s korištenjem stranice.';
            $session = password_hash(time(), PASSWORD_BCRYPT);
            //  Session is valid for 1 hour
            setcookie('mojDucan_session', $session, (time()+60*60),'/');//, '/', 'mojducan.duckdns.org', 1
            //  Username is saved for 24 hours
            setcookie("mojDucan_username",$email,(time()+24*60*60),'/');
            
            //  Update user's session in database
            $stmt = $conHandle->prepare("UPDATE korisnici SET session = ? WHERE emailStr = ?") or die("Error binding");
            $stmt->bind_param("ss", $session, $email);
            $stmt->execute();
        } else {
            $errorMsg = 'Neispravna kombinacija korisničkog imena i lozinke, pokušajte ponovo.';
        }
        
    }
    else
        $errorMsg = "Pogrešni podaci za prijavu";
    
    echo $errorMsg;
?>
