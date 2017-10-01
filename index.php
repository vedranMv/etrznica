<!DOCTYPE html>
<html>
<head>
<?php
    include "php/connFile.php";
    $page = "";
    
    //  Get page arguments (page name, filter data...)
    if(isset($_SERVER["QUERY_STRING"]) && !(empty($_SERVER["QUERY_STRING"])) )
    {
        $qstr = $_SERVER["QUERY_STRING"];
        $qstr = @explode("&", $qstr);
        
        $page = @explode("=", $qstr[0]);
        $page = $page[1];
    }
    
    //  Chekc if user is logged in and has an opened session
    if (isset($_COOKIE['mojDucan_username']))
        $user = $_COOKIE['mojDucan_username'];
    else
        $user = "";
    
    if (isset($_COOKIE['mojDucan_session']))
        $session = $_COOKIE['mojDucan_session'];
    else 
        $session = "";
        
 ?>
 
<meta charset="UTF-8">
<title>Dobrodošli u Vaš on-line dućan</title>
<link href="css/style.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="js/stdlib.js" ></script>
</head>
<body>

    <?php 
        //  Switch between login menu and user-menu
        if ($session == "")
        {
            echo '
            <div id="cont_user" onmouseenter="extend()" onmouseleave="contract()" style="width:150px;" >
            	<a href="?page=register"> Ponudi svoje proizvode </a>
            	<br/>
            	<br/>
            	<a href="?page=login" style="margin-top: 30px;"> Prijavi se </a>
            </div>
            ';
        }
        else
        {
            //  One every page refresh, extend user's session
            setcookie('mojDucan_session', $session, (time()+60*60),'/');
            echo '
            <div id="cont_user" onmouseenter="extend()" onmouseleave="contract()" style="text-align: right;" >
            	<a class="link_menu" href="?page=dodajP"> Dodaj proizvod </a>
            	<a class="link_menu" href="?page=admin" > Upravljaj proizvodima </a>
            	<a class="link_menu" href="?page=settings" > Potavke računa </a>
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
	        	      
	        	      $quer = explode("=", $qstr[2]);
	        	      $GLOBALS["overrideQuery"] = $quer[1];
	        	      
	        	      
	        	      include "php/ponuda.php";
	        	  }
	        	
	        	?>
				
	 
	        </div>
	    </div>
	
	   <!-- Rezultati -->
	    <div id="cont_pregled">
		<?php 
		  //  Load the page 
		  if (($page != "") && ($page != "connFile") && file_exists($page.'.php'))
		      include ($page.'.php');
		  else
		      include ('home.php');
		?>
	    </div>
	    
	    <div style="clear:both;"></div>
    </div>
    
    
    <div id="cont_footer">
    </div>
    
</body>
</html>