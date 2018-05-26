# Guess

A decentralized quiz platform.

## Requirement of tools

- node
- npm

## Install:
### install tools
```bash

1、git clone https://github.com/allsportscoin/guess.git

2、cd  guess/tools

3、npm install
```

## Tutorial

### layout
directory layout:

```
+ src 
  | - myguess.sol // The source code of contract
+ tools
  | - contract_guess.js
  | - getGuessInfo.js  //tools for get detail inforamtion of guess
  | - package.json  
```
## How
### How to use the tool
```bash

node getGuessInfo.js [the txhash of guess]
```
### output:

```bash
{"status":"success", "match_id":"764397","gusser":"0xaddress of guesser","result":"1","value":"100"}
```
