<script>
	$('.tmibizlist tr').bind('click',function(){
		tr = $(this);
		id = tr.find(".tmibiz").html();
		mszam3 = tr.find(".tmszam3").html();
		beerk.selectTask(id,mszam3);
	})
	
/*
	$('#bOBeerkMenu').bind('click',function () {
		//showMenu();
		beerk.showReview();
	})
*/	


	
	$('#bGumi, #bGumiFelni, #bFelni').on('click', function(event){

		event.stopPropagation();
        event.preventDefault();
        if(event.handled !== true) {
			clickHelp();
            beerk.showPozPanel($(this));
            event.handled = true;
        } else {
            return false;
        }
		

	});
	
	$('#bJavitas').bind('click',function () {
		beerk.rszJavitas();
	})
	$('#bElteres').bind('click',function (event) {
		event.stopPropagation();
        event.preventDefault();
        if(event.handled !== true) {
			clickHelp();
			beerk.reviewFilter();
            event.handled = true;
        } else {
            return false;
        }
	})
	
	$('#bFolytMost').bind('click',function (event) {
		event.stopPropagation();
        event.preventDefault();
        if(event.handled !== true) {
			clickHelp();
			$('#divreview').hide();
			$('#divpanel').show();
            event.handled = true;
        } else {
            return false;
        }
	})
	$('#bFolytKesobb').bind('click',function (event) {
		event.stopPropagation();
        event.preventDefault();
		clickHelp();
        if(event.handled !== true) {

            beerk.folytKesobb();

            event.handled = true;
        } else {
            return false;
        }
		
	})	
	$('#bLezar').bind('click',function () {
		beerk.lezarStart();
	})	
	$('#bMenu').bind('click',function () {
		showMenu();
	})	
	$('#bGPanelClose').bind('click',function () {
		beerk.closeGPanel(true);
	})	
	$('#bGPanelCancel').bind('click',function () {
		beerk.closeGPanel(false);
	})	
	$('#srendszam').bind('click',function (event) {
		clickHelp();
        beerk.showReview()	
	})	
	$('.rszadatok').bind('click',function (event) {
		event.stopPropagation();
        event.preventDefault();
        if(event.handled !== true) {
			clickHelp();
			beerk.showGPanel();
            event.handled = true;
        } else {
            return false;
        }

	})	
	
	
</script>
<body>
<div id=divclick></div>
<input type=hidden id=hAZON>
<input type=hidden id=hMIBIZ>
<input type=hidden id=hSORSZ>
<input type=hidden id=rendszam>
<div id=tploBeerk>
	<div id=divheader>
		Örzött beérkezés
	</div>
	<div id=divcontent>
		<!-- tasklist -->
		<div id=divmibizlist>
			<button id=bMenu>Menü</button>
			<table class=tmibizlist>
				<tr>
				<th>Sofőr</th>
				<th>Bizonylatszám</th>
				</tr>
				
				<{sorok}>
			</table>
		</div>
		<!-- tasklist end -->
		<!-- panel -->
		<div id=divpanel>
			<div class='drendszam'>
				<span id=srendszam></span>
			</div>
			<div class='rszadatok'>
				<!--
				<div class='dceg'>
					<span class='labelCeg'>Cég:</span>
					<span class='dataCeg'></span>
				</div>
				-->
				<div class='dmeretminta'>
					<span class='dataMeret'></span>
				</div>
				<div class='dfegu'>
					<span class='labelFelniGumi'>Felni/Gumi:</span>
					<span class='dataFegu'></span>
				</div>
			</div>
			<div id=divgpanel>
				<div class='labelGP'>A</div>
				<select id=gpMarkaA></select>
				<select id=gpMeretA></select>
				<select id=gpMintaA></select>
				<select id=gpSIA></select>
				<br>
				<div class='labelGP'>B</div>
				<select id=gpMarkaB></select>
				<select id=gpMeretB></select>
				<select id=gpMintaB></select>
				<select id=gpSIB></select>
				<br>
				<div class='labelGP'>P</div>
				<select id=gpMarkaP></select>
				<select id=gpMeretP></select>
				<select id=gpMintaP></select>
				<select id=gpSIP></select>
				<br>
				
				<div class='labelGP'>felni</div>
				<select id=gpFelnitip>
						<option value='-'>nincs</option>
						<option value='A'>Alu</option>
						<option value='L'>Lemez</option>
				</select>
				<br>
				
				<button id=bGPanelClose>Mentés</button>
				<button id=bGPanelCancel>Mégsem</button>
			</div>
			<div class='dcontrol'>
				<div class=divlabels>
					<div class=ddrbvart>
						<span class='labelDrbVart'>várt</span>
						<span class='dataDrbVart'>0</span>
					</div>
					<div class=ddrbkesz>
						<span class='labelDrbKesz'>lepakolva</span>
						<span class='dataDrbKesz'>0</span>
					</div>
				</div>
				<div class=dButtons>
					<button class='bprint' id=bGumi>Gumi</button>
					<button class='bprint' id=bGumiFelni>Gumi+Felni</button>
					<button class='bprint' id=bFelni>Felni</button>
					<button class='bnoprint' id=bJavitas>Javítás</button>
				</div>
			</div>
		</div>
		<!-- panel end -->
		<!-- melysegmeres panel -->
		<div id=divmeres>
		</div>
		<!-- melysegmeres panel end -->
		<div id=divreview>
			<div class=divReviewTables>
			<div class=divreviewLeft>
				<!--
				<div class=divreviewLeftFilter>
					<button id=letter1>1</button>
					<button id=letter2>2</button>
					<button id=letter3>3</button>
				</div>
				-->
				<div class='divreviewLeftContent'>
					<table class=tableReviewFilter>
						<tbody>
						</tbody>
					</table>
				</div>
				
			</div>
			<div class=divreviewRight>
			<div class=divTableReview>
			<table class=tableReview></table>
			</div>
			</div>
			<div id=divhiany>
				<div class=labelHiany></div>
				<div class=dataHiany></div>
			</div>
			<div class=dButtonsReview>
				<button id=bElteres>Eltérések</button>
				<button id=bFolytMost>Folyt. most</button>
				<button id=bFolytKesobb>Folyt. később</button>
				<button id=bLezar>Lezárás</button>
			</div>			
		</div>
		
	</div>
</div>

</body>