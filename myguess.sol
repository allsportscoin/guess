pragma solidity ^0.4.16;

contract owned {
    address public owner;

    function owned() public {
        owner = msg.sender;
    }

    modifier onlyOwner {
        require(msg.sender == owner);
        _;
    }
    
    function changeOwner(address newOwner) public onlyOwner {
        require(newOwner != 0x00);
        owner = newOwner;
    }

}

contract  MyGuess is owned {

    enum Result {Home, Draw, Away}
    enum Status {Close, Open}
    
    struct MatchBet {
        uint256 home;
        uint256 draw;
        uint256 away;
        uint256 total_amount;
        uint256 total_guesser;
    }
    
    //mapping(uint => Guess) public  Guesses;
    mapping(uint => MatchBet) public  Guesses;
    mapping(uint => string) public MatchId2Name;
    mapping(uint => Result) public matchResults;
    mapping(uint => Status) public guessStatus;

    uint public total_match=0;
 
    function changeName(uint _mid, string _name) public onlyOwner{
        MatchId2Name[_mid] = _name;
        total_match++;
    }
    
    function setMatchResult(uint _mid, Result _result) public onlyOwner{
        require(_checkResult(_result));
        matchResults[_mid] = _result;
    }

    
    function setGuessStatus(uint _mid, Status _status) public onlyOwner{
        require(_status == Status.Open || _status == Status.Close);
        guessStatus[_mid] = _status;
    }


    function addGuess(uint _mid, address _gusser,  Result _result, uint256 _value) public onlyOwner returns(uint){
        //require(guessStatus[_mid] == Status.Open);
        require(_value > 0);
        require(_gusser != 0x00);
        require(_checkResult(_result));
        MatchBet bet = Guesses[_mid];

        bet.total_guesser += 1;
        bet.total_amount += _value;

        if(Result.Home == _result){
            bet.home += _value;
        }else if(Result.Draw == _result){
            bet.draw += _value;
        }else{
            bet.away += _value;
        }
        
        Guesses[_mid] = bet;
        
        return _mid;
        
    }

    function addMultiGuesses(uint _mid, address[] _guesseres, Result[] _results, uint256[] _values
                                ,uint _count) public onlyOwner returns(uint) {
        require(_count > 0 && _count <= 20);
        
        for(uint i=0; i <_count; i++){
            addGuess(_mid, _guesseres[i], _results[i], _values[i]);
        }
        return Guesses[_mid].total_guesser;
    }
    
    function _checkResult(Result _result) internal returns(bool){
        return _result == Result.Home || _result == Result.Draw || _result == Result.Away;
    }
}


