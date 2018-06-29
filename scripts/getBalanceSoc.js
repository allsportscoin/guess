var env = process.env.ENV;

var contract = require('./contract_devsoc.js');
if(env == 'product' || env == 'beta'){
  contract = require('./contract_soc.js');
}

var web3 = contract.web3;
var args = process.argv.splice(2);
if(typeof(args[0]) != 'undefined'){
  var address = args[0];
}else{
  console.log('{"status":"error"}');
}

var balance = contract.myContract.balanceOf(address)
console.log('{"balance":' + balance.toNumber()/1000000000000000000 + '}');
