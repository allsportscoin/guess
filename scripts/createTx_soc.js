var  contract = require('./contract_soc.js');


var web3 = contract.web3;
var Tx = require('ethereumjs-tx');
var contractAddress = contract.contractAddress;

function transfer(toAddr, value, count){
  var data = contract.myContract.transfer.getData(toAddr, value);
  var rawTx = {
    from: fromAddr,
    to: contractAddress,
    gasPrice: web3.toHex(12100000000),
    gasLimit: web3.toHex(60000),
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
  var fromAddr = args[2];
  var privateKey = new Buffer(args[3], 'hex');
  try {
    var value = parseFloat(args[1]);
    var socbalance = contract.myContract.balanceOf(fromAddr);
    var sb_num = socbalance.toNumber();
    if(sb_num < value) {
      console.log('{"status":"error", "errmsg":"insufficient soc"}');
    } else {
      var count = web3.eth.getTransactionCount(fromAddr);
      transfer(args[0], args[1], count);
    }
  } catch(err) {
    console.log('{"status":"error", "errmsg":"' + err.message + '"}');
  }
}


