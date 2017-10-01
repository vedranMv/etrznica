
Za postavljanje vlastitih proizvoda na stranicu potrebno je registrirati se
kako biste kasnije proizvode mogli ukloniti ili izmjeniti. Naziv koji upišete 
biti će prikazan kao naziv prodavatelja uz svaki vaš proizvod kao i kontakt.
Kontakt informacije možete ostavti po želji, facebook ime, broj telefona, 
goluba pismonoše... Mjesto i županija nisu obavezni podaci ali će vam pomoći
stupiti u kontakt s ljudima u vašoj blizini i time olakšati dostavu stoga je
poželjno unesti te podatke. Email-adresa so koristi isključivo za prijavu na
stranicu i nije javno dostupna
<br\>
Za registraciju popunite podatke ispod
<form id="form_register"  method="post" action="php/regScript.php" >

<p>Email i lozinka za prijavu na stranicu, zbog vaše sigurnosti
preporuča se upotreba lozinke koju ne koristite na nekoj drugoj stranici.</p><br/>
 <table>
  <tr>
    <td>Email za prijavu*</td>
    <td><input required="required" type="email" name="email" placeholder="Vaša e-mail adressa"/></td>
  </tr>
    <tr>
    <td>Lozinka za prijavu*</td>
    <td><input required="required" type="password" name="passwd" placeholder="Lozinka" /></td>
  </tr>
</table> 
	 
	  <br/>
	<p>Podaci o vama</p><br/>
	 <table>
      <tr>
        <td>Naziv prodavača*</td>
        <td><input required="required" type="text" name="naziv" placeholder="Vaš naziv"/></td>
      </tr>
      <tr>
        <td>Kontakt*</td>
        <td><textarea required="required" name="kontakt" rows="3" cols="60" placeholder="Kontak informacije dostupne pri pregledu vaših proizvoda"></textarea><br/></td>
      </tr>
      <tr>
        <td>Županija prodaje</td>
        <td>
        	<select name="zupanija">
        		<option value="1">Zagrebačka županija</option>
                <option value="2">Krapinsko-zagorska županija</option>
                <option value="3">Sisačko-moslavačka županija</option>
                <option value="4">Karlovačka županija</option>
                <option value="5">Varaždinska županija</option>
                <option value="6">Koprivničko-križevačka županija</option>
                <option value="7">Bjelovarsko-bilogorska županija</option>
                <option value="8">Primorsko-goranska županija</option>
                <option value="9">Ličko-senjska županija</option>
                <option value="10">Virovitičko-podravska županija</option>
                <option value="11">Požeško-slavonska županija</option>
                <option value="12">Brodsko-posavska županija</option>
                <option value="13">Zadarska županija</option>
                <option value="14">Osječko-baranjska županija</option>
                <option value="15">Šibensko-kninska županija</option>
                <option value="16">Vukovarsko-srijemska županija</option>
                <option value="17">Splitsko-dalmatinska županija</option>
                <option value="18">Istarska županija</option>
                <option value="19">Dubovačko-neretvanska županija</option>
                <option value="20">Međimurska županija</option>
                <option value="21">Grad Zagreb</option>
        	</select>
		</td>
      </tr>
      <tr>
        <td>Mjesto prodaje</td>
        <td><input type="text" name="mjesto" placeholder="Mjesto"/></td>
      </tr>
    </table> 

	<br/>
	<br/>
	<input type="button" onclick="submitForm(this.form)" name="register" value="Registracija"  />
	<br/>
    	<div id="form_register_status">
    
    	</div>
	<br/>
	
</form>