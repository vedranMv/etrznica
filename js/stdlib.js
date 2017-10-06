
////////////////////////////////////////////////////////////////////////////////
////	AJAX calls to PHP scripts
////////////////////////////////////////////////////////////////////////////////

/**
 * Update content of search engine container on the lft on the window
 * @param arg
 * @param act Switch variable to customize request
 * @returns
 */
function updateContent(arg, act)
{
	var request;
	var header;

	
	if (act == 1)
	{
		header = arg;
		script = "php/results.php";
		result = "cont_pregled";
	}
	else if (act == 2)
	{
		header = document.getElementById(arg).value;
		if (header == "")
			header = "%";
		
		header = "query="+header;
		
		script = "php/ponuda.php";
		result = "cont_rezultati";
	}
	

	if (window.XMLHttpRequest) request=new XMLHttpRequest();
	else request=new ActiveXObject("Microsoft.XMLHTTP");
	
	request.onreadystatechange=function()
	{
		if (request.readyState==4 && request.status==200)
		{
			document.getElementById(result).innerHTML = request.responseText;
		}
	}
	
	request.open("POST",script,true);
	request.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	request.send(header);
}


/**
 * General form submision through ajax
 * @param form Form object which is being submitted
 * @param id Additional argument which some forms supply, default = 0
 * @returns
 */
function submitForm(form, id=0)
{
	//	Regular expression for validating format of e-mail
	var emailFormat = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    
	var request;
	var header = "";
	var statusBar = form.id+"_status";
	var formData = new FormData(form);
	var valid = true;

	document.getElementById(statusBar).innerHTML="Obrada u tijeku, molimo pričekajte";
	setTimeout('document.getElementById("'+statusBar+'").innerHTML="";',5000);
	
	if (id != 0)
		formData.append("id", id);
	
	//	Validate all required fields
	for(var i=0;i<form.length;i++)
	{
		if (form[i].type!="button")
		{
			//	Email field requires special way of validation via regex 
			if (form[i].name == "email")
			{
				if (!emailFormat.test(form[i].value))
					valid = false;
			}
			else if (form[i].required && (form[i].value == ""))
				valid = false;
		}	
	}
	
	//	Stop registration if content is not valid
	if (!valid)
	{
		document.getElementById(statusBar).innerHTML = "Molimo ispravno popunite sva obavezna polja označena *";
		return;
	}

	
	if (window.XMLHttpRequest) request=new XMLHttpRequest();
	else request=new ActiveXObject("Microsoft.XMLHTTP");
	
	request.onreadystatechange=function()
	{
		if (request.readyState==4 && request.status==200)
		{
			document.getElementById(statusBar).innerHTML = request.responseText;
			if (((request.responseText[0] == 'U') || //Uspiješno
				(request.responseText[0] == 'V')) &&
				(form.id != "form_changesettings"))	//Vaš proiz...
				form.reset();
			
			if ((form.id == "form_login") && (request.responseText[0] == 'P'))
				setTimeout('window.location.href = "?page=home";',1000);
		}
	}
	request.open("POST",form.action,true);
	request.send(formData);
};

//	Holds div ID of the element that was updated on the last call of the
//	function below
var oldElementId = "";
/**
 * Functions fetches data about the product from DB in order to populate form
 * for editing product data
 * @param id Product ID for which data is requested
 * @param el Div element with product name, used to change the background once
 * it's clicked in order to highlight it
 * @returns
 */
function fetchPdata(id, el)
{
	var request;
	var header = "id="+id;
	
	document.getElementById(el.id).style.backgroundImage = 'url("../imgs/black30.png")';
	
	if (oldElementId != "")
		document.getElementById(oldElementId).style.backgroundImage = '';
	oldElementId = el.id;
	

	if (window.XMLHttpRequest) request=new XMLHttpRequest();
	else request=new ActiveXObject("Microsoft.XMLHTTP");
	
	request.onreadystatechange=function()
	{
		if (request.readyState==4 && request.status==200)
		{
			document.getElementById("subcont_prodinfo").innerHTML = request.responseText;
		}
	}
	request.open("POST","php/editPScript.php",true);
	request.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	request.send(header);
}


/**
 * Delete user account through AJAX
 * @param status 
 * @returns
 */
function deleteSw(switcher, pid)
{
	var request;
	var header;
	var script = "";
	var response;
	var redirect ="";
	
	//	Configure arguments for AJAx call
	if (switcher == "account")
	{
		response = confirm("Jeste li sigurni da želiti obrisati račun, \n ovaj proces je nepovratan?");
		script = "php/deleteUser.php";
		header = "delete=true";
		redirect = "?page=home";
	}
	else if (switcher == "product")
	{
		response = confirm("Jeste li sigurni da želiti ukloniti odabrani proizvod, \n ovaj proces je nepovratan?");
		script = "php/deleteProduct.php";
		header = "delete=true&pid="+pid;
		redirect = "?page=editP";
	}
	
	if (response == false)
	{
		return false;
	}
	

	if (window.XMLHttpRequest) request=new XMLHttpRequest();
	else request=new ActiveXObject("Microsoft.XMLHTTP");
	
	request.onreadystatechange=function()
	{
		if (request.readyState==4 && request.status==200)
		{
			alert(request.responseText);
			setTimeout('window.location.href = "'+redirect+'";',1000);
		}
	}
	request.open("POST",script,true);
	request.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	request.send(header);
}

/**
 * Delete user account through AJAX
 * @param status 
 * @returns
 */
function deleteAccount(status)
{
	var request;
	var header = "delete=true";
	
	var response = confirm("Jeste li sigurni da želiti obrisati račun, \n ovaj proces je nepovratan?");
	
	if (response == false)
	{
		return false;
	}
	

	if (window.XMLHttpRequest) request=new XMLHttpRequest();
	else request=new ActiveXObject("Microsoft.XMLHTTP");
	
	request.onreadystatechange=function()
	{
		if (request.readyState==4 && request.status==200)
		{
			alert(request.responseText);
			setTimeout('window.location.href = "?page=home";',1000);
		}
	}
	request.open("POST","php/deleteUser.php",true);
	request.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	request.send(header);
}

////////////////////////////////////////////////////////////////////////////////
////	Drop-down menu top-right
////////////////////////////////////////////////////////////////////////////////

function alterContent(hovering)
{
	if (hovering)
	{
		document.getElementById("cont_user").innerHTML= 
			'   <a class="link_menu2" href="?page=register" style=""> Registriraj se </a> \
            	<a class="link_menu2" href="?page=login" style=""> Prijavi se </a>';
	}
	else
	{
		document.getElementById("cont_user").innerHTML= 
			'   <a class="link_menu2 href="?page=register"> Ponudi svoje proizvode </a>';
	}
	
}

////////////////////////////////////////////////////////////////////////////////
////	Misc. functions
////////////////////////////////////////////////////////////////////////////////

/**
 * Allows a form to be submitted by pressing enter in an <input> field which has
 * this function called through onKeyPress event
 * @param e Event information, used to extract key code of the pressed key
 * @param form Form element used for starting submitForm AJAX call
 * @returns
 */
function submitByEnter(e, form)
{
	var keyCode = (e.keyCode ? e.keyCode : e.which);
	
	if (keyCode == 13)
		submitForm(form);
}
