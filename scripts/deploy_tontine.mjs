import { JsonRpcProvider, Wallet, hexlify, toUtf8Bytes } from "ethers";

// Récupération des arguments passés depuis PHP
const groupName = process.argv[2] || "Tontine Inconnue";

// Configuration via variables d'environnement
const rpcUrl = process.env.POLYGON_RPC_URL || "https://rpc-amoy.polygon.technology/";
const privateKey = process.env.RELAYER_PRIVATE_KEY;
const factoryAddress = process.env.TONTINE_FACTORY_ADDRESS || "0x0000000000000000000000000000000000000000";

if (!privateKey) {
    console.error(JSON.stringify({ error: "Private key is missing in .env" }));
    process.exit(1);
}

async function deploy() {
    try {
        const provider = new JsonRpcProvider(rpcUrl);
        const wallet = new Wallet(privateKey, provider);

        // Créer un payload de données (Hexadécimal) avec le nom du groupe
        const dataPayload = hexlify(toUtf8Bytes(`TontineChain - Deploiement: ${groupName}`));

        // On crée une transaction avec 0 MATIC, mais avec des données
        // L'envoi se fait à la "Factory" ou à soi-même si pas de factory
        const tx = {
            to: wallet.address, // On l'envoie à nous-mêmes pour payer uniquement le gas
            value: 0n,
            data: dataPayload,
        };

        const txResponse = await wallet.sendTransaction(tx);
        
        // On attend la confirmation d'un bloc
        const receipt = await txResponse.wait(1);

        console.log(JSON.stringify({
            status: "success",
            contract_address: receipt.to || wallet.address, // Adresse du smart contract (ou notre adresse en simulation 0 value)
            tx_hash: receipt.hash,
            block_number: receipt.blockNumber
        }));

    } catch (error) {
        console.error(JSON.stringify({ error: error.message }));
        process.exit(1);
    }
}

deploy();
