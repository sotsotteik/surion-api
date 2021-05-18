

/*
web3.eth.getAccounts(function(error, accounts) {
if(error) {
console.log(error);
}

web3.eth.getBalance(accounts[0]).then(function(result){
console.log( "Balance : " ,web3.utils.fromWei(result, 'ether'));

});
});
*/
const connectButton = async () => {
      try {
          const newAccounts = await ethereum.request({
              method: 'eth_requestAccounts'
          })
          console.log(newAccounts);
      } catch (error) {
          console.error(error)
      }
}

const accountButton = async () => {
    try {

    accounts = await ethereum.request({
        method: 'eth_accounts',
        })
        web3.eth.getBalance(accounts[0]).then(function(result){
        console.log( "Balance : " ,web3.utils.fromWei(result, 'ether'));
        });
        console.log(accounts);

        var testERC721 = new web3.eth.Contract(testERC721ABI, testERC721Address);


    } catch (error) {
        console.error(error)
    }
}

const swapButton = async () => {

    try {
        var exchange = new web3.eth.Contract(exchangeABI, exchangeAddress);
        console.log(exchange.methods);

    } catch (error) {
        console.error(error)
    }

}

const hashButton = async () => {

        try {

            console.log(accounts[0])





            var exchange = new web3.eth.Contract(exchangeABI, exchangeAddress);
            var registry = new web3.eth.Contract(registryABI, registryAddress);


console.log(registryAddress)
console.log(accounts[0])
console.log(exchangeAddress)

            
                var test = await exchange.methods.hashOrder_(
                    registryAddress, // registry
                    accounts[0], // maker address
                    ZERO_ADDRESS, // static target address
                    '0x00000000',// static selector
                    '0x', // static extra data 
                    '1', // maximum fill
                    '0', // listing time
                    '1000000000000', // expiration time
                    '1' // salt

                ).call()
                    

            console.log(test);
            
        } catch (error) {
            console.error(error)
        }

}

const ownerButton = async () => {
    try {


        var testERC721 = new web3.eth.Contract(testERC721ABI, testERC721Address);
        var totalSupply = await testERC721.methods.totalSupply().call();

        var i = 1;

        while (i <= totalSupply) {
            var owner = await testERC721.methods.ownerOf(i).call();
            console.log('NFT : '+ i + ' - '+ owner)
            i++;
          }

    } catch (error) {
        console.error(error)
    }
}

const approveOrderButton = async () => {

    try {
        let nfts = [1,2]


        var atomicizer = new web3.eth.Contract(atomicizerABI, atomicizerAddress);
        var exchange = new web3.eth.Contract(exchangeABI, exchangeAddress);
        var registry = new web3.eth.Contract(registryABI, registryAddress);
        var statici = new web3.eth.Contract(staticABI, staticAddress);

        const testERC20 = new web3.eth.Contract(testERC20ABI, testERCAddress)
        var testERC721 = new web3.eth.Contract(testERC721ABI, testERC721Address);



        var approveOrder = await exchange.methods.approveOrder_(
            registryAddress, 
            '0xbeeea2e615438d4c7df34f5f7eec5beee8186fad', 
            exchangeAddress, 
            '0x00000000', 
            '0x', 
            '1', 
            '0', 
            '1000000000000', 
            '1010', 
            false).send({from: '0xbeeea2e615438d4c7df34f5f7eec5beee8186fad'})


        console.log(approveOrder)
        /*


        const selector = web3.eth.abi.encodeFunctionSignature('swapOneForOneERC721(bytes,address[7],uint8[2],uint256[6],bytes,bytes)')
        const paramsOne = web3.eth.abi.encodeParameters(
            ['address[2]', 'uint256[2]'],
            [[erc721.address, erc721.address], [nfts[0], nfts[1]]]
        )
        */

    } catch (error) {
        console.log(error)
    }

}

const validateOrderButton = async () => {
    try {

        var exchange = new web3.eth.Contract(exchangeABI, exchangeAddress);



        var hashOrder = await exchange.methods.hashOrder_(
            registryAddress, 
            '0xbeeea2e615438d4c7df34f5f7eec5beee8186fad', 
            exchangeAddress, 
            '0x00000000', 
            '0x', 
            '1', 
            '0', 
            '1000000000000', 
            '1010', 
        ).call({
            from: '0xbeeea2e615438d4c7df34f5f7eec5beee8186fad'}
        );


        let valid = await exchange.methods.validateOrderParameters_(registryAddress, 
            '0x823CA7fdf5c6B114577244Cd9106e1665c5E5750', 
            exchangeAddress, 
            '0x00000000', 
            '0x', 
            '1', 
            '0', 
            '1000000000000', 
            '1010').call({
                from: '0x823CA7fdf5c6B114577244Cd9106e1665c5E5750'
            });

        
        console.log(valid)

        
    } catch (error) {
        console.log(error)
    }
}

const surionButton = async () => {
    try {

        var surion = new web3.eth.Contract(surionABI, transparentUpgradeableProxyAddress);

        var version = await surion.methods.version().call();
        console.log(version);

    } catch (error) {
        console.log(error)
    }
}


const mintSurionButton = async () => {
    try {

        accounts = await ethereum.request({
            method: 'eth_accounts',
            })
            
        console.log(accounts);

        var surion = new web3.eth.Contract(surionABI, transparentUpgradeableProxyAddress);
        var totalSupply = await surion.methods.totalSupply().call();

        var mintTransaction = await surion.methods.safeMint(accounts[0], ++totalSupply).send({
            from: accounts[0]
        });
        console.log(mintTransaction);

    } catch (error) {
        console.log(error)
    }
}














































  