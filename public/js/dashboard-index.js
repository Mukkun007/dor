const stepButtons = document.querySelectorAll(".step-button");
const progress = document.querySelector("#progress");

Array.from(stepButtons).forEach((button, index) => {
    button.addEventListener("click", () => {
    progress.setAttribute(
        "value",
        (index * 100) / (stepButtons.length - 1)
    ); //there are 3 buttons. 2 spaces.

    stepButtons.forEach((item, secindex) => {
        if (index > secindex) {
        item.classList.add("done");
        }
        if (index < secindex) {
        item.classList.remove("done");
        }
    });
    });
});

stepButtons.forEach((button, index) => {
    button.addEventListener("click", () => {
        // Créez un tableau pour stocker les chemins d'URL correspondant à chaque étape
        const paths = [
            "{{ path('dashboard_index') }}",           // Accueil
            "{{ path('dashboard_paiement') }}",       // Paiement
            "{{ path('dashboard_modeLivraison') }}",  // Mode de livraison
            "{{ path('dashboard_livraison') }}"       // Livraison
        ];

        // Récupérez le chemin d'URL correspondant à l'index de l'étape actuelle
        const path = paths[index];

        // Changez l'URL en utilisant la méthode pushState pour que l'historique du navigateur soit correct
        window.history.pushState({ path: path }, '', path);
    });
});

document.getElementById("paiementForm").addEventListener("submit", function(event) {
    // Assurez-vous que le formulaire est soumis avec la méthode POST
      if (this.method.toUpperCase() !== 'POST') {
          console.error('La méthode de la requête n\'est pas POST.');
          return;
      }
      
      // Empêchez la soumission normale du formulaire
      event.preventDefault();
      let formData = new FormData(this);
      // Envoie les données du formulaire via AJAX
        fetch("{{ path('dashboard_paiement') }}", {
          method: "POST",
          body: formData // Utilisez FormData pour récupérer les données du formulaire multipart
      })
      .then(response => {
          // Vérifie si la réponse est OK
          if (!response.ok) {
              throw new Error('Une erreur est survenue lors de la requête.');
          }
          // La réponse est OK, affichez un message de succès
      
          console.log("Données envoyées avec succès !");
          console.log("Emetteur:", formData.get('paiement[emetteur]'));
          console.log("Cheque:", formData.getAll('paiement[cheque][]'));
          // Vous pouvez rediriger l'utilisateur ou faire d'autres actions ici si nécessaire
      })
      .catch(error => {
          // Affichez l'erreur dans la console
          console.error('Erreur:', error);
          // Affichez un message d'erreur à l'utilisateur ou prenez d'autres mesures nécessaires
      });
  });
