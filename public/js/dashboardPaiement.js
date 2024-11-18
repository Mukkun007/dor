function toggleFields() {
        var choice = document.getElementById("choice_recuperation").value;
        if (choice === "2") {
            document.getElementById("recuperation_siege").classList.remove("d-none");
      
        } else {
            
            document.getElementById("recuperation_siege").classList.add("d-none");
            
        }
    }
function toggleFieldsMobile() {
        var choice = document.getElementById("choice_recuperationMobile").value;
        if (choice === "2") {
            document.getElementById("recuperation_siegeMobile").classList.remove("d-none");
      
        } else {
            
            document.getElementById("recuperation_siegeMobile").classList.add("d-none");
            
        }
    }

function toggleRadio(){
    let depotBanqueRadio = document.getElementById('paiemenDepotBanque');
    let surPlaceRadio = document.getElementById('paiementSurPlace');
    let unTiersRadio = document.getElementById('paiementUnTiers');
    const choice_recuperation = document.querySelector('#choice_recuperation');
    const choice_rt = document.querySelector('#choice_rt');
    const nameTierce = document.querySelector('#nameTierce');
    const firstnameTierce = document.querySelector('#firstnameTierce');
    const cinTierce = document.querySelectorAll('.cinTierce');

    let depotBanqueRadioMobile = document.getElementById('paiemenDepotBanqueMobile');
    let surPlaceRadioMobile = document.getElementById('paiementSurPlaceMobile');
    let unTiersRadioMobile = document.getElementById('paiementUnTiersMobile');
    const choice_recuperationMobile = document.querySelector('#choice_recuperationMobile');
    const choice_rtMobile = document.querySelector('#choice_rtMobile');
    const nameTierceMobile = document.querySelector('#nameTierceMobile');
    const firstnameTierceMobile = document.querySelector('#firstnameTierceMobile');
    const cinTierceMobile = document.querySelectorAll('.cinTierceMobile');

  if (depotBanqueRadio.checked) {
      // Si l'utilisateur choisit dépot à la banque
      document.querySelector('#lieuRecuperation').classList.add('d-none');
      document.querySelector('#tiers_info').classList.add('d-none');

      choice_recuperation.required = false;
      choice_rt.required = false;
      nameTierce.required = false;
      firstnameTierce.required = false;
      cinTierce.required = false;


  } else if (surPlaceRadio.checked) {
      // Si l'utilisateur choisit récupération sur place
      document.querySelector('#lieuRecuperation').classList.remove('d-none');
      document.querySelector('#tiers_info').classList.add('d-none');


      choice_recuperation.required = true;
      choice_rt.required = true;
      nameTierce.required = false;
      firstnameTierce.required = false;
      cinTierce.required = false;

  
  } else if (unTiersRadio.checked) {
      // Si l'utilisateur choisit récupération par un tiers
    document.querySelector('#lieuRecuperation').classList.remove('d-none');
    document.querySelector('#tiers_info').classList.remove('d-none');

    choice_recuperation.required = true;
      choice_rt.required = true;
      nameTierce.required = true;
      firstnameTierce.required = true;
      cinTierce.forEach(input => {
      input.required = true;
    });

    
  }

    if (depotBanqueRadioMobile.checked) {
        // Si l'utilisateur choisit dépot à la banque
    
        document.querySelector('.dashboardPaiement-mobile #lieuRecuperationMobile').classList.add('d-none');
        document.querySelector('.dashboardPaiement-mobile #tiers_infoMobile').classList.add('d-none');


        choice_recuperationMobile.required = false;
        choice_rtMobile.required = false;
        nameTierceMobile.required = false;
        firstnameTierceMobile.required = false;
        cinTierceMobile.required = false;

    } else if (surPlaceRadioMobile.checked) {

        document.querySelector('#lieuRecuperationMobile').classList.remove('d-none');
        document.querySelector('#tiers_infoMobile').classList.add('d-none');

        choice_recuperationMobile.required = true;
        choice_rtMobile.required = true;
        nameTierceMobile.required = false;
        firstnameTierceMobile.required = false;
        cinTierceMobile.required = false;

    } else if (unTiersRadioMobile.checked) {

    document.querySelector('#lieuRecuperationMobile').classList.remove('d-none');
    document.querySelector('#tiers_infoMobile').classList.remove('d-none');
  

    choice_recuperationMobile.required = true;
        choice_rtMobile.required = true;
        nameTierceMobile.required = true;
        firstnameTierceMobile.required = true;
        cinTierceMobile.forEach(input => {
        input.required = true;
    });
    }
}

function toggleElements() {
    let choixRecuperationGET = document.querySelector('input[name="paiement[choixRecuperation]"]:checked').value;
    let choice_meetingMobileGET = document.getElementById('choice_recuperationMobile').value;
    let choice_meetingGET = document.getElementById('choice_recuperation').value;
   

    if (choice_meetingGET === "2") {
        document.getElementById("recuperation_siege").classList.remove("d-none");
  
    } else {
        
        document.getElementById("recuperation_siege").classList.add("d-none");
        
    }
    if (choice_meetingMobileGET === "2") {
        document.getElementById("recuperation_siegeMobile").classList.remove("d-none");
  
    } else {
        
        document.getElementById("recuperation_siegeMobile").classList.add("d-none");
        
    }


    // Afficher ou masquer les éléments en fonction du choix de récupération
    if(choixRecuperationGET === 'depotBanque'){
        
        const paiementSurPlace = document.querySelector('#paiementSurPlace');
        paiementSurPlace.disabled=true;
        
        const paiementSurPlaceMobile = document.querySelector('#paiementSurPlaceMobile');
        paiementSurPlaceMobile.disabled=true;
        const paiementUnTiersMobile = document.querySelector('#paiementUnTiersMobile');
        paiementUnTiersMobile.disabled=true;

        const paiementUnTiers = document.querySelector('#paiementUnTiers');
        paiementUnTiers.disabled=true;
    }
    else if (choixRecuperationGET === 'surPlace') {
        document.querySelector('#lieuRecuperation').classList.remove('d-none');
        document.querySelector('#tiers_info').classList.add('d-none');
        document.querySelector('.dashboardPaiement-mobile #lieuRecuperationMobile').classList.remove('d-none');
        document.querySelector('.dashboardPaiement-mobile #tiers_infoMobile').classList.add('d-none');
       
        const paiemenDepotBanqueMobile = document.querySelector('#paiemenDepotBanqueMobile');
        paiemenDepotBanqueMobile.disabled=true;

        const paiemenDepotBanque = document.querySelector('#paiemenDepotBanque');
        paiemenDepotBanque.disabled=true;

        const paiementUnTiersMobile = document.querySelector('#paiementUnTiersMobile');
        paiementUnTiersMobile.disabled=true;

        const paiementUnTiers = document.querySelector('#paiementUnTiers');
        paiementUnTiers.disabled=true;


    } else if (choixRecuperationGET === 'tierce') {
        document.querySelector('#lieuRecuperation').classList.remove('d-none');
        document.querySelector('#tiers_info').classList.remove('d-none');
        document.querySelector('.dashboardPaiement-mobile #lieuRecuperationMobile').classList.remove('d-none');
        document.querySelector('.dashboardPaiement-mobile #tiers_infoMobile').classList.remove('d-none');

       
        const paiemenDepotBanqueMobile = document.querySelector('#paiemenDepotBanqueMobile');
        paiemenDepotBanqueMobile.disabled=true;

        const paiemenDepotBanque = document.querySelector('#paiemenDepotBanque');
        paiemenDepotBanque.disabled=true;

        const paiementSurPlace = document.querySelector('#paiementSurPlace');
        paiementSurPlace.disabled=true;
        
        const paiementSurPlaceMobile = document.querySelector('#paiementSurPlaceMobile');
        paiementSurPlaceMobile.disabled=true;

    }
    
   
    
}
toggleElements();
// function onPageLoad() {
//   // Appeler la fonction pour gérer l'état initial
//   toggleElements();
  
//   // Ajouter un écouteur d'événements pour gérer les changements dans les champs de radio
//   document.querySelectorAll('input[name="paiement[choixRecuperation]"]').forEach(function(radio) {
//       radio.addEventListener('change', toggleElements);
//   });
  

  
// }

// // Appeler onPageLoad lorsque la page est entièrement chargée
// window.onload = onPageLoad;

$(document).ready(function () {
    
        fc1 = $('.fc-1'),
        numericKeys = '0123456789';

    fc1.on('keypress', function (event) {
        if (event.charCode === 0) {
            return;
        }

        if (-1 === numericKeys.indexOf(event.key)) {
            event.preventDefault();
        }
    });
});

$(document).ready(function() {
    // Fonction pour vérifier si tous les champs sont remplis
    function checkFieldsFilled() {
        var allFieldsFilled = true;

        // Vérification pour chaque champ
        $('input[type="text"]').each(function() {
            if ($(this).val() === '') {
                allFieldsFilled = false;
                return false; // Sortir de la boucle si un champ est vide
            }
        });

        // Retourner l'état de remplissage de tous les champs
        return allFieldsFilled;
    }

    // Vérifier une fois que le document est chargé et à chaque changement de champ
    $('input[type="text"]').on('input', function() {
        // Si tous les champs sont remplis, masquer le bouton "Enregistrer"
        if (checkFieldsFilled()) {
            $('#proceder').hide();
        } else {
            $('#proceder').show();
        }
    });
});