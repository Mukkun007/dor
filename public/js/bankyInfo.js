let cinInputs;
let ribInputs;
let ribBordereau;

document.addEventListener('DOMContentLoaded', function () {
    cinInputs = document.querySelectorAll('input.user_cin');
    ribInputs = document.querySelectorAll('input.user_rib');
    ribBordereau = document.querySelector('.ribUser');
    
    ribInputs.forEach(function (input, index) {
        input.addEventListener('input', function () {
            const rib_Value = getRibValue();
            displayBankInfo(rib_Value);
        });
    });
    const ribBordereauValue = getRibBordereauValue(ribBordereau);
    displayBankInfo(ribBordereauValue);
});

function getCinValue() {
    let cinValue = '';
    cinInputs.forEach(function (input) {
        cinValue += input.value;
    });

    return cinValue;
}

function getAllRibValue() {
    let allRibValue = '';
    ribInputs.forEach(function (input) {
        allRibValue += input.value;
    });

    return allRibValue;
}

function getRibValue() {
    let ribValue = '';
    ribInputs.forEach(function (input) {
        if (input.value === '') {
            ribValue += '-';
        } else {
            ribValue += input.value;
        }
    });

    return ribValue.slice(0, 5);
}

function getRibBordereauValue() {
    const ribBordereauV = ribBordereau.textContent.replace(/\s+/g, '');
    return ribBordereauV.slice(0, 5);
}

function displayBankInfo(ribValue) {
    let bankInfo = '';
    switch (ribValue) {
        case '00004':
            bankInfo = "Banque Malgache de l’Océan Indien - BMOI Madagascar";
            break;
        case '00005':
            bankInfo = "BNI Madagascar";
            break;
        case '00006':
            bankInfo = "The Mauritius Commercial Bank (Madagascar) SA - MCB Madagascar";
            break;
        case '00007':
            bankInfo = "SBM Madagascar";
            break;
        case '00008':
            bankInfo = "Société Générale Madagasikara";
            break;
        case '00009':
            bankInfo = "Bank of Africa Madagascar - BOA Madagascar";
            break;
        case '00011':
            bankInfo = "Accès Banque Madagascar - ABM";
            break;
        case '00012':
            bankInfo = "BGFIBank Madagascar";
            break;
        case '00013':
            bankInfo = "Baobab Banque Madagascar";
            break;
        case '00014':
            bankInfo = "Banky First BM Madagascar";
            break;
        case '00015':
            bankInfo = "Société d’Investissement pour la Promotion des Entreprises à Madagascar - SIPEM BANQUE";
            break;
        case '00016':
            bankInfo = "MVOLA Banque";
            break;
        case '00017':
            bankInfo = "AFG BANK MADAGASCAR - AFG BANK";
            break;
        case '00997':
            bankInfo = "Centre des Chèques Postaux - CCP";
            break;
        case '00998':
            bankInfo = "Direction Générale du Trésor - DGT";
            break;
        case '00999':
            bankInfo = "Banky Foiben’i Madagasikara - BFM";
            break;
        default:
            bankInfo = "";
    }
    const bankInfoDiv = document.querySelector('.bank-info');
    bankInfoDiv.textContent = bankInfo;
}