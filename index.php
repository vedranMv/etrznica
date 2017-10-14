<!DOCTYPE html>
<html>
<head>
<?php
//  Include basic files for connecting to DB and managing user's session
require_once "php/connFile.php";
require_once "php/sessionManager.php";
    $page = "";
    
    //  Read query string to get page arguments (page name, filter data...)
    if(isset($_SERVER["QUERY_STRING"]) && !(empty($_SERVER["QUERY_STRING"])) )
    {
        //  Break query string into form ['key1=value1', 'key1=value2',...]
        $qstr = $_SERVER["QUERY_STRING"];
        $qstr = @explode("&", $qstr);
        //  Expect first key-value pair to denote page
        $page = @explode("=", $qstr[0]);
        if ($page[0] === "page"){
            //Loop through page name and remove all non-alpha characters to
            //  prevent user from tampering with the URL
            if (ctype_alpha($page[1])) {
                $page = $page[1];
            } else {
                $page = "";
            }
        }
        $cnt = 0;
        $token = "";
        while (isset($qstr[$cnt])) {
            if (strstr($qstr[$cnt], 'token=') !== FALSE) {
                //  Split token=***** and save value after = sign
                $token = @explode("=", $qstr[$cnt]);
                $token = $token[1];
               
            }
            $cnt++;
        }
    }
    
    //  Chekc if user is logged in and has an opened session
    $user = getUserCookie();
    $session = getSessionCookie();
    $validSes = hasValidSession();
    //echo "User: ".$user."<br/>Sesion:".$session."<br/>Valid:".$validSes;
 ?>
 
    <meta charset="UTF-8">
    <title>Dobrodošli u e-Tržnicu, vašu on-line tržnicu</title>
    <link href="css/style.css" rel="stylesheet" type="text/css" />
    <script type="text/javascript" src="js/stdlib.js" ></script>
</head>

<body <?php if (!$validSes) { echo 'onmouseenter="alterContent(false);"'; } ?> >
    <?php 
        //  Switch between login menu and user-menu
        if (!$validSes) {
            echo '
            <div id="cont_user" onmouseenter="alterContent(true);" onmouseleave="alterContent(false);" style="width:150px;" >
            	<a class="link_menu2" href="?page=register"> Ponudi svoje proizvode </a>
            </div>
            ';
        }
        else {
            //  On every page refresh, extend user's session
            setcookie($sessionCookie, $session, (time()+60*60), '/');
            echo '
            <div id="cont_user" style="text-align: right;" >
            	<a class="link_menu" href="?page=addP"> Dodaj proizvod </a>
            	<a class="link_menu" href="?page=editP" > Upravljaj proizvodima </a>
            	<a class="link_menu" href="?page=editU" > Potavke računa </a>
            	<a class="link_menu" href="?page=logout"> Odjavi se </a>
            </div>
            ';
        }
    ?>
    <div id="cont_header">
    	<a href="?page=home"><img alt="" src="imgs/title.png"/></a>
    </div>
    <div style="clear:both;"></div>
    
    <div id="cont_wrapper">
        <!-- Lijevi menu s tražilicom -->
	    <div id="cont_brzo_trazenje">
	    	<input 	id="input_trazilica" 
	    			onkeyup="updateContent('input_trazilica', 2);"
	    			class="search_box" type="text" name="qsearch" 
	    			placeholder="Što tražite?...Koja županija?..." />
	    	
	        <div id="cont_rezultati">
	        	<?php 
	        	  if ($page !== "ponuda")
	        	  {
	        	      echo '<script type="text/javascript">
					           updateContent('."'input_trazilica'".', 2);
				            </script>';
	        	  }
	        	  else
	        	  {
	        	      //  We need to access these variables from include file
	        	      $filt = @explode("=", $qstr[1]);
	        	      $GLOBALS["overrideFilter"] = $filt[1];
	        	      
	        	      $quer = @explode("=", $qstr[2]);
	        	      $GLOBALS["overrideQuery"] = $quer[1];
	        	      
	        	      include "php/ponuda.php";
	        	  }
	        	?>
	        </div>
	    </div>
	
	   <!-- Rezultati -->
	    <div id="cont_pregled">
		<?php 
		  //  Loading of the main page within this div container
		  if (($page != "") && 
		      ($page != "connFile") && 
		      ($page != "ponuda") &&
		      file_exists($page.'.php')) {
		      include ($page.'.php');
		  }
		  else if ($page != "ponuda") {
		      include ('home.php');
		  }
		?>
	    </div>
	    <div style="clear:both;"></div>
    </div>
    
    <div id="cont_footer">
    	| <a href="?page=info">Informacije</a> | 2017 |
    
</body>
</html>