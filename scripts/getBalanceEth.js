var contract = require('./contract_soc.js');

var web3 = contract.web3;
var args = process.argv.splice(2);
if(typeof(args[0]) != 'undefined'){
  var address = args[0];
}else{
  console.log('{"status":"error"}');
}

var balance = web3.eth.getBalance(address);
console.log('{"balance":' + balance.toNumber()/1000000000000000000 + '}');
