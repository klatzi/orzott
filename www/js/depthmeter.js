var depthMeterDialog = {
	show: function(){
		panelName = 'printer';
		$.get( "css/"+panelName+".css", function( data ) {
			css = '<head><style>' + data + '</style></head>';
			$.get( "views/"+panelName+".tpl", function( data ) { 
				tpl = data; 
				$('#divContent').html(css + tpl);
				$('#divContent').show();
				depthMeterDialog.createTable();				
			});
			
		})
	},
	createTable:function(){
				sorok = "";
				app.depthMeters.forEach(function(meter){
					sorok+="<tr>";
					sorok+="<td class=printerid>"+meter.id+"</td>";
					if (printer.name=='undefined')	meter.name="";
					sorok+="<td class=printername>"+meter.name+"</td>";
					sorok+="</tr>";
					
				})
				if (sorok=="") {showMenu();}
				else {
					$("#tableprinter tbody").html(sorok);
					$('#tableprinter tr').bind('click',function(){
						tr = $(this);
						if (app.depthMeterConnected) app.manageConnection2(false);
						app.depthMeterId = tr.find(".printerid").html();
						app.depthMeterName = tr.find(".printername").html();
						app.BT2Enabled();						
						showMenu();
					})
				}
				

	}
}