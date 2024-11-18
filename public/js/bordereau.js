document.addEventListener('DOMContentLoaded', function () {
    const banque = document.getElementById("banque");

    function bankInfoBordereau(ribValue) {
        let bankInfo = '';
        let ribPrefix = ribValue.substring(0, 5);
        switch (ribPrefix) {
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
        return bankInfo;
    }

    banque.textContent = banque.textContent + bankInfoBordereau(ribValue);
    console.log(banque);
});

// Create a new Date object
let today = new Date();

// Get the current date
let day = today.getDate();
let month = today.getMonth() + 1;
let year = today.getFullYear();

// Function to add leading zeros if necessary
function addLeadingZero(number) {
    return number < 10 ? '0' + number : number;
}

// Format the date
let formattedDate = addLeadingZero(day) + '-' + addLeadingZero(month) + '-' + year;

const currentDateSpan = document.getElementById("currentDate");
currentDateSpan.textContent = formattedDate;