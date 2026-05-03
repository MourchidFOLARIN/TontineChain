// SPDX-License-Identifier: MIT
pragma solidity ^0.8.20;

import "@openzeppelin/contracts/metatx/ERC2771Context.sol";

contract TontineGroup is ERC2771Context {
    address[] public members;
    uint256 public contributionAmount; // USDC amount
    uint256 public frequencySeconds;
    uint256 public startTimestamp;
    
    uint256 public currentCycle = 1;
    mapping(uint256 => mapping(address => bool)) public hasContributed;
    mapping(uint256 => uint256) public cycleTotalContributions;
    
    event ContributionRecorded(address indexed member, uint256 cycle, string txRef);
    event PayoutReleased(uint256 cycle, address indexed beneficiary, uint256 amount);

    constructor(
        address[] memory _members,
        uint256 _contributionAmount,
        uint256 _frequencySeconds,
        uint256 _startTimestamp,
        address _trustedForwarder
    ) ERC2771Context(_trustedForwarder) {
        members = _members;
        contributionAmount = _contributionAmount;
        frequencySeconds = _frequencySeconds;
        startTimestamp = _startTimestamp;
    }

    // Enregistrement de la cotisation par le backend (après succès FedaPay/KKiaPay)
    function recordContribution(address member, uint256 amount, string calldata txRef) external {
        require(amount >= contributionAmount, "Amount too low");
        require(!hasContributed[currentCycle][member], "Already contributed this cycle");

        hasContributed[currentCycle][member] = true;
        cycleTotalContributions[currentCycle]++;

        emit ContributionRecorded(member, currentCycle, txRef);
    }

    function isCycleComplete(uint256 cycle) public view returns (bool) {
        return cycleTotalContributions[cycle] == members.length;
    }

    // Libération de la cagnotte vers le bénéficiaire
    function releasePayout(uint256 cycle) external {
        require(isCycleComplete(cycle), "Cycle not complete");
        
        // Le bénéficiaire est déterminé par sa position dans l'array (cycle 1 = index 0)
        address beneficiary = members[cycle - 1];
        uint256 totalAmount = contributionAmount * members.length;
        
        // La logique de transfert de token ERC20 (USDC) serait ici
        // IERC20(usdcToken).transfer(beneficiary, totalAmount);

        emit PayoutReleased(cycle, beneficiary, totalAmount);
        
        if(currentCycle < members.length) {
            currentCycle++;
        }
    }
    
    function getMemberScore(address member) external pure returns (uint256) {
        // Logique de score on-chain (si requise)
        return 100;
    }
}
