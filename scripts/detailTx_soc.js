var  contract = require('./contract_soc.js');


var web3 = contract.web3;
var args = process.argv.splice(2);
if(typeof(args[0]) != 'undefined'){
  var input=args[0];
}else{
  console.log('{"status":"error"}');
}
const InputDataDecoder = require('ethereum-input-data-decoder');
const decoder = new InputDataDecoder(contract.abi);
const result = decoder.decodeData(input);
console.log('{"status":"success", "to":"0x'+result.inputs[0]+'","value":"'+result.inputs[1].toString()+'"}');
