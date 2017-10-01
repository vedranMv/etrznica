<h2>Dodavanje novih proizvoda u sustav</h2>
<p>Popunite navedene informacije o proizvodu kojeg nudite i pritisnite tipku "Dodaj" pri dnu stranice</p>

<form id="form_addproduct"  method="post" action="php/addProd.php" >
    <table>
        <tr>
            <td>Naziv proizvoda*</td>
            <td><input required="required" type="text" name="nazivP" placeholder="Naziv"/></td>
        </tr>
        <tr>
            <td>Opis proizvoda*</td>
            <td><textarea required="required" name="opisP" rows="6" cols="100" placeholder="Opis proizvoda..."></textarea><br/></td>
      </tr>
        <tr>
            <td>Slika</td>
            <td><input type="file" name="slikaP" placeholder="Lokacija slike" size="1024" /></td>
        </tr>
    </table> 

	<br/>
	<br/>
	<input type="button" onclick="submitForm(this.form)" name="dodaj" value="Dodaj"  />
	<br/>
    	<div id="form_addproduct_status">
    	</div>
</form>