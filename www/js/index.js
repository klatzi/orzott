/*
 * Licensed to the Apache Software Foundation (ASF) under one
 * or more contributor license agreements.  See the NOTICE file
 * distributed with this work for additional information
 * regarding copyright ownership.  The ASF licenses this file
 * to you under the Apache License, Version 2.0 (the
 * "License"); you may not use this file except in compliance
 * with the License.  You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing,
 * software distributed under the License is distributed on an
 * "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY
 * KIND, either express or implied.  See the License for the
 * specific language governing permissions and limitations
 * under the License.
 */
function printer (pid,pname) {
	this.id =pid;
	this.name = pname;
	}

var app = {
	printerId:"",
	printerName:"",
	printerConnected:false,
	printers : [],
    // Application Constructor
    initialize: function() {
        this.bindEvents();
    },
    // Bind Event Listeners
    //
    // Bind any events that are required on startup. Common events are:
    // 'load', 'deviceready', 'offline', and 'online'.
    bindEvents: function() {
        document.addEventListener('deviceready', this.onDeviceReady, false);
    },
    // deviceready Event Handler
    //
    // The scope of 'this' is the event. In order to call the 'receivedEvent'
    // function, we must explicitly call 'app.receivedEvent(...);'
    onDeviceReady: function() {
        app.receivedEvent('deviceready');
    },
    // Update DOM on a Received Event
    receivedEvent: function(id) {
		document.addEventListener("backbutton", this.onBackKeyDown, false);
		showLogin();
        console.log('Received Event: ' + id);
		
    },
	onBackKeyDown:function() {
    },	
	/* bt */
	getPrinters:function(){
		if(typeof bluetoothSerial != 'undefined'){ 
				bluetoothSerial.list(function(devices) {
					app.printers.length=0;
					devices.forEach(function(device) {
						if (device.class=='1664') {
							p = new printer(device.id,device.name); 
							app.printers.push(p);
							//app.printerId = device.id;app.printerName=device.name;app.manageConnection(true)
						}
					})
					printerDialog.show();
				})
				
			
		}
		else {
			alert('bt serial undefined');
			printerDialog.show();
		}	
		
	},
	BTEnabled:function(){
	
	
		if(typeof bluetoothSerial != 'undefined'){ 
			var btAssign = function() {
				app.manageConnection(true);
			}
			bluetoothSerial.isEnabled(
				btAssign,
				function(){alert('bluetooth not enabled')}
			); 

		}
		else {
			alert('bt serial undefined');
		}
	},
	BTDisabled:function(){
		app.manageConnection(false);
	},
	
   manageConnection: function(needConnect) {
		if(typeof bluetoothSerial != 'undefined') {
			// connect() will get called only if isConnected() (below)
			// returns failure. In other words, if not connected, then connect:
			var connect = function () {
				// attempt to connect:
				bluetoothSerial.connect(
					app.printerId,  // device to connect to
					app.openPort,    // start listening if you succeed
					app.showError    // show the error if you fail
				);
			};

			// disconnect() 
			var disconnect = function () {
				bluetoothSerial.disconnect(
					app.closePort,     // stop listening to the port
					app.showError      // show the error if you fail
				);
			};

			// here's the real action of the manageConnection function:
			if (needConnect) bluetoothSerial.isConnected(null, connect);
			else bluetoothSerial.isConnected(disconnect,null);
		}
    },
/*
    subscribes to a Bluetooth serial listener for newline
    and changes the button:
*/
    openPort: function() {
        // if you get a good Bluetooth serial connection:
        console.log("Connected to: " + app.printerId);
		app.printerConnected=true;
        // set up a listener to listen for newlines
        // and display any new data that's come in since
        // the last newline:
        bluetoothSerial.subscribe('\n', function (data) {
            console.log(data);
			//alert(data);
        });
    },

/*
    unsubscribes from any Bluetooth serial listener and changes the button:
*/
    closePort: function() {
        // if you get a good Bluetooth serial connection:
        // unsubscribe from listening:
		app.printerConnected=false;
        bluetoothSerial.unsubscribe(
                function (data) {
                    //alert(data);
					console.log(data);
                },
                app.showError
        );
    },
/*
    appends @error to the message div:
*/
    showError: function(error) {
		console.log("bluetooth error:"+error);
        //alert(error);
    }
	
	
	/* bt eddig*/
};
if(!window.cordova){
	//alert('cordova not found');
} 
else {
	//alert('cordova ok');
}
app.initialize();

