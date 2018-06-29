# About this project

This project is the Server of the Soc guess project, built in php. We use mysql and redis to store some tmp data for preformance.
Use Yaf to routing the http request.


## Directory

```
.
├── README.md
├── config     // project config
├── library       // some local api
├── webroot/index.php    // entry file
├── modules/Inner/controllers  //code for request controllers
├── scripts/contract_guess.js    // abi of the guess contract
├── scripts/contract_soc.js   //abi of the soc contract
├── scripts/createTx_guess.js    // put guess info to block chain
├── scripts/createTx_soc.js    //send soc to guess winner
├── scripts/package.json    //some dependencies pkgs, use npm install to get all dependencies.
├── service   //main logic of this project
└── Bootstrap.php
```



## How to use

1. Login etherscan.io, create api-key token, and fill into setting.ini.  Publish your guess contract, then put address and private key to scripts/createTx_guess.js, include contract address.
2. Prepare some eth address, and insert into database(seperate into some groups, each group contains three addresses). Also need two addresses used to return award, fill into setting.ini. Tips: All this address should have some eth for gas fee.
3. Create database and tables use db.sql
4. Config host and port of mysql or redis in env.ini
5. Add some crontabs in server machine, such as scanAllTx, checkGamble, checkIncharge, checkPrize, checkTransfer, openPrize, setMatchStatus,  eg: /home/work/php7/bin/php webroot/cli.php scanTx scanAllTx
6. Now you can add match and match games.

