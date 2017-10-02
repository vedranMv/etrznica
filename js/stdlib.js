


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
		header = "query="+document.getElementById(arg).value;
		
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



function submitForm(form, id=0)
{
	var emailFormat = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    
	var request;
	var header = "";
	var statusBar = form.id+"_status";
	var formData = new FormData(form);
	var valid = true;
	alert("Yo "+statusBar);
	setTimeout('document.getElementById("'+statusBar+'").innerHTML="";',5000);
	
	if (id != 0)
		formData.append("id", id);
	
	//	Validate all required fields
	for(var i=0;i<form.length;i++)
	{
		if (form[i].type!="button")
		{
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
			document.getElementById(statusBar).innerHTML += request.responseText;
			if ((request.responseText[0] == 'U') || //Uspiješno
				(request.responseText[0] == 'V'))	//Vaš proiz...
				form.reset();
			
			if (form.id == "form_login")
				setTimeout('window.location.href = "?page=home";',1500);
		}
	}
	request.open("POST",form.action,true);
	request.send(formData);
	
	
};

function fetchPdata(id)
{
	var request;
	var header = "id="+id;
	
	//document.getElementById("subcont_prodinfo").innerHTML = "";

	if (window.XMLHttpRequest) request=new XMLHttpRequest();
	else request=new ActiveXObject("Microsoft.XMLHTTP");
	
	request.onreadystatechange=function()
	{
		if (request.readyState==4 && request.status==200)
		{
			document.getElementById("subcont_prodinfo").innerHTML = request.responseText;
		}
	}
	request.open("POST","php/adminForm.php",true);
	request.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	request.send(header);
}

////////////////////////////////////////////////////////////////////////////////
////	Drop-down menu top-right
////////////////////////////////////////////////////////////////////////////////
function extend ()
{
	document.getElementById("cont_user").style.height = "100px";
}

function contract ()
{
	document.getElementById("cont_user").style.height = "50px";
}
