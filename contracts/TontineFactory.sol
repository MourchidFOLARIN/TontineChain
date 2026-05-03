// SPDX-License-Identifier: MIT
pragma solidity ^0.8.20;

import "./TontineGroup.sol";

contract TontineFactory {
    event TontineCreated(address indexed tontineAddress, address[] members, uint256 contributionAmount);

    // EIP-2771 Trusted Forwarder pour les transactions Gasless
    address public immutable trustedForwarder;

    constructor(address _trustedForwarder) {
        trustedForwarder = _trustedForwarder;
    }

    function createTontine(
        address[] memory _members,
        uint256 _contributionAmount,
        uint256 _frequencySeconds,
        uint256 _startTimestamp
    ) external returns (address) {
        TontineGroup newTontine = new TontineGroup(
            _members,
            _contributionAmount,
            _frequencySeconds,
            _startTimestamp,
            trustedForwarder
        );

        emit TontineCreated(address(newTontine), _members, _contributionAmount);
        
        return address(newTontine);
    }
}
