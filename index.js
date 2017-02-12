var express = require('express');
var app = express();

if (process.argv.length < 7) {
	console.log('Usage: node index.js <asterisk-extension> <asterisk-domain> <did-international-code> <ami-username> <ami-password>');
	process.exit(1);
}

var targetExtension = process.argv[2];
var domain = process.argv[3];
var intlCode = process.argv[4];
var amiUsername = process.argv[5];
var amiPassword = process.argv[6];

console.log('Starting up VoIP.ms SMS Relay');
console.log('Domain: ' + domain);
console.log('International Code: ' + intlCode);
console.log('Target Extension: ' + targetExtension);
console.log('AMI Username: ' + amiUsername);
console.log('AMI Password: ' + amiPassword);
 
app.get('/incoming_message', function(req, res) {
  res.json({action: "Success"})
  console.log(req.query)
  ami.send({ action: 'MessageSend', To: 'sip:' + targetExtension, From: '<sip:' + intlCode + req.query.from + '@' + domain + '>', Body: req.query.message});
})

var AsteriskAmi = require('asterisk-ami');
var ami = new AsteriskAmi( { host: 'localhost', username: amiUsername, password: amiPassword, debug: true } );

ami.connection.on('connect', function(){
   console.log('Connected');
});

ami.on('login', function(err, event){
   console.log('Logged in');
});

ami.connect(); 
app.listen(1990)