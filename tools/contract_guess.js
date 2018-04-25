const contract = module.exports = {};

const EthereumTx = require('ethereumjs-tx');
var Web3 = require('web3');
var web3 = new Web3(new Web3.providers.HttpProvider('http://geth_node_ip:8545'));

var abi = [
	{
		"constant": false,
		"inputs": [
			{
				"name": "_mid",
				"type": "uint256"
			},
			{
				"name": "_gusser",
				"type": "address"
			},
			{
				"name": "_result",
				"type": "uint8"
			},
			{
				"name": "_value",
				"type": "uint256"
			}
		],
		"name": "addGuess",
		"outputs": [
			{
				"name": "",
				"type": "uint256"
			}
		],
		"payable": false,
		"stateMutability": "nonpayable",
		"type": "function"
	},
	{
		"constant": false,
		"inputs": [
			{
				"name": "_mid",
				"type": "uint256"
			},
			{
				"name": "_guesseres",
				"type": "address[]"
			},
			{
				"name": "_results",
				"type": "uint8[]"
			},
			{
				"name": "_values",
				"type": "uint256[]"
			},
			{
				"name": "_count",
				"type": "uint256"
			}
		],
		"name": "addMultiGuesses",
		"outputs": [
			{
				"name": "",
				"type": "uint256"
			}
		],
		"payable": false,
		"stateMutability": "nonpayable",
		"type": "function"
	},
	{
		"constant": false,
		"inputs": [
			{
				"name": "_mid",
				"type": "uint256"
			},
			{
				"name": "_name",
				"type": "string"
			}
		],
		"name": "changeName",
		"outputs": [],
		"payable": false,
		"stateMutability": "nonpayable",
		"type": "function"
	},
	{
		"constant": false,
		"inputs": [
			{
				"name": "newOwner",
				"type": "address"
			}
		],
		"name": "changeOwner",
		"outputs": [],
		"payable": false,
		"stateMutability": "nonpayable",
		"type": "function"
	},
	{
		"constant": false,
		"inputs": [
			{
				"name": "_mid",
				"type": "uint256"
			},
			{
				"name": "_status",
				"type": "uint8"
			}
		],
		"name": "setGuessStatus",
		"outputs": [],
		"payable": false,
		"stateMutability": "nonpayable",
		"type": "function"
	},
	{
		"constant": false,
		"inputs": [
			{
				"name": "_mid",
				"type": "uint256"
			},
			{
				"name": "_result",
				"type": "uint8"
			}
		],
		"name": "setMatchResult",
		"outputs": [],
		"payable": false,
		"stateMutability": "nonpayable",
		"type": "function"
	},
	{
		"constant": true,
		"inputs": [
			{
				"name": "",
				"type": "uint256"
			}
		],
		"name": "Guesses",
		"outputs": [
			{
				"name": "home",
				"type": "uint256"
			},
			{
				"name": "draw",
				"type": "uint256"
			},
			{
				"name": "away",
				"type": "uint256"
			},
			{
				"name": "total_amount",
				"type": "uint256"
			},
			{
				"name": "total_guesser",
				"type": "uint256"
			}
		],
		"payable": false,
		"stateMutability": "view",
		"type": "function"
	},
	{
		"constant": true,
		"inputs": [
			{
				"name": "",
				"type": "uint256"
			}
		],
		"name": "guessStatus",
		"outputs": [
			{
				"name": "",
				"type": "uint8"
			}
		],
		"payable": false,
		"stateMutability": "view",
		"type": "function"
	},
	{
		"constant": true,
		"inputs": [
			{
				"name": "",
				"type": "uint256"
			}
		],
		"name": "matchId2Name",
		"outputs": [
			{
				"name": "",
				"type": "string"
			}
		],
		"payable": false,
		"stateMutability": "view",
		"type": "function"
	},
	{
		"constant": true,
		"inputs": [
			{
				"name": "",
				"type": "uint256"
			}
		],
		"name": "matchResults",
		"outputs": [
			{
				"name": "",
				"type": "uint8"
			}
		],
		"payable": false,
		"stateMutability": "view",
		"type": "function"
	},
	{
		"constant": true,
		"inputs": [],
		"name": "owner",
		"outputs": [
			{
				"name": "",
				"type": "address"
			}
		],
		"payable": false,
		"stateMutability": "view",
		"type": "function"
	},
	{
		"constant": true,
		"inputs": [],
		"name": "total_match",
		"outputs": [
			{
				"name": "",
				"type": "uint256"
			}
		],
		"payable": false,
		"stateMutability": "view",
		"type": "function"
	}
]

contractAddress = '0xd3f5a5f5c91b74d0af066f69accb11d3d2ec3bb3';
contract.abi = abi;
contract.myContract = web3.eth.contract(abi).at(contractAddress);
contract.web3 = web3;
contract.contractAddress = contractAddress;

