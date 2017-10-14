<h2>Dodavanje novih proizvoda u sustav</h2>
<p>Popunite navedene informacije o proizvodu kojeg nudite i pritisnite tipku "Dodaj proizvod" pri dnu stranice.
Vaše kontakt informacija koje ste unjeli tijekom registracije će automatski
biti dodane kod prikazivanja proizvoda.</p>

<form id="form_addproduct"  method="post" action="php/addProd.php" >
    <table>
        <tr>
            <td>Naziv proizvoda*</td>
            <td><input required="required" type="text" name="nazivP" placeholder="Naziv"/></td>
        </tr>
        <tr>
            <td>Opis proizvoda*</td>
            <td><textarea required="required" name="opisP" rows="6" cols="80" placeholder="Opis proizvoda, dostupna količina, cijena..."></textarea><br/></td>
      </tr>
        <tr>
            <td>Slika</td>
            <td>
            	<input type="file" class="file_generic" name="slikaP" placeholder="Lokacija slike" />
            	<br/>
            	<span style="font-size: 12px;">Dozvoljen format slika: .jpg, .jpeg, .png, .gif</span> <br/>
            	<span style="font-size: 12px;">Vaša će slika nakon postavljanja biti smanjena na 300 x 300 px</span>
            </td>
        </tr>
    </table> 

	<br/>
	<br/>
	<input type="button" class="button_generic" onclick="submitForm(this.form)" name="dodaj" value="Dodaj proizvod"  />
	<br/>
    	<div id="form_addproduct_status">
    	</div>
</form>