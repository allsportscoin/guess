var  contract = require('./contract_guess.js');

var fromAddr=contract.fromAddr;
var privateKey=contract.privateKey;

var web3 = contract.web3;
var count = web3.eth.getTransactionCount(fromAddr);
var Tx = require('ethereumjs-tx');
var contractAddress = contract.contractAddress;

function transfer(mid, toAddr, result, value, count){
  var data = contract.myContract.addGuess.getData(mid, toAddr, result, value);
  var rawTx = {
    from: fromAddr,
    to: contractAddress,
    gasPrice: web3.toHex(8100000000),
    gasLimit: web3.toHex(600000),
    data: data,
    nonce: web3.toHex(count),
  }

  var tx = new Tx(rawTx);
  tx.sign(privateKey);

  var serializedTx = tx.serialize();
  web3.eth.sendRawTransaction('0x' + serializedTx.toString('hex'), function(err, hash) {
    if (!err){
      console.log('{"status":"0", "txhash":"'+hash+'"}');
    }else{
      console.log('{"status":"error", "errmsg":"' + err + '"}');
    }
  }
  );
}


var args = process.argv.splice(2);
if(typeof(args[0]) != "undefined" && typeof(args[1]) != "undefined" && typeof(args[2]) != "undefined" && typeof(args[3]) != "undefined"){
  transfer(args[0], args[1], args[2], args[3], count);
}

