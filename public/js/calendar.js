// import nonClickableDatesData from './ferie.json';
// const nonClickableDates = nonClickableDatesData.nonClickableDates;

let rendezVousMatinParDate = {};
let rendezVousSoirParDate = {};
isAndroid = platform.os.family === "Android";
isIOS = platform.os.family === "iOS";
var datecom = null;
const annule = document.getElementById("annuler");

const nonClickableDates = [
    "2024-01-01",
    "2024-03-08",
    "2024-03-29",
    "2024-03-31",
    "2024-04-01",
    "2024-04-10",
    "2024-05-01",
    "2024-05-09",
    "2024-05-19",
    "2024-05-20",
    "2024-06-26",
    "2024-08-15",
    "2024-11-01",
    "2024-12-25",
    "2024-06-14"
];

const generateModal = (id, title, content) => {
    const newModal = document.createElement("div");
    newModal.innerHTML = `
    <div class="modal fade edit-form show" id="${id}" tabindex="-1" aria-labelledby="exampleModalLabel" style="padding-right: 0.416626px; display: block;">
      <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                    <h5 class="modal-title" id="${id}">${title}</h5>
                    <button type="button" class="btn-ferme" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>${content}</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" id="gen-modal-dismiss">OK</button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal-backdrop fade show" id="generated"></div>
  `;
    document.body.appendChild(newModal);

    const modalDismissButton = newModal.querySelector("#gen-modal-dismiss");
    modalDismissButton.addEventListener("click", () => {
        newModal.style.display = "none";
        document.querySelector(".modal-backdrop").style.display = "none";
    });
};

document.addEventListener("DOMContentLoaded", function () {
    // fetch("ferier.json")
    // .then((response) => response.json())
    // .then((data) => {
    //   const nonClickableDates = data.nonClickableDates;

    const today = new Date();
    /*
    Recuperer les order
    */
    const orders = [];
    fetch(
        document
            .getElementById("myForm")
            .getAttribute("action")
            .replace("modeLivraison", "getOrder")
    )
        .then((response) => {
            return response.json();
        })
        .then((data) => {
            data.forEach((orderData) => {
                let order = {
                    title: orderData.type,
                    start: orderData.date,
                };
                orders.push(order);
                myEvents.push(order);
            });
            calendar.refetchEvents();
        })
        .catch((error) => {
            console.error("Erreur lors de la récupération des commandes :", error);
        });

    const calendarEl = document.getElementById("calendar");
    const myModal = new bootstrap.Modal(document.getElementById("form"));
    const dangerAlert = document.getElementById("danger-alert");
    const close = document.querySelector(".btn-close");
    const ferme = document.getElementById("ferme");
    let datesDisponibles = [];

    const myEvents = JSON.parse(localStorage.getItem("events")) || [
        {
            id: uuidv4(),
            title: `Edit Me`,
            start: "2023-04-11",
            editable: false,
        },
        {
            id: uuidv4(),
            title: `Delete me`,
            start: "2023-04-17",
            editable: false,
        },
    ];

    const calendar = new FullCalendar.Calendar(calendarEl, {
        header: {
            center: "customButton",
            // right: "today, prev,next ",
            right: "aujourd'hui, prev, next",
        },
        plugins: ["dayGrid", "interaction"],
        editable: true,
        selectable: true,
        unselectAuto: false,
        displayEventTime: false,
        events: myEvents,
        // validRange: {start: dateDebut, end: dateFin},
        validRange: {start: today, end: dateFin},
        locale: 'fr',
        datesSet: function (info) {
            const currentMonth = info.view.currentStart.getMonth() + 1;
            const currentYear = info.view.currentStart.getFullYear();
            if (
                (currentYear === parseInt(dateDebut.substring(0, 4)) &&
                    currentMonth < parseInt(dateDebut.substring(5, 7))) ||
                (currentYear === parseInt(dateFin.substring(0, 4)) &&
                    currentMonth > parseInt(dateFin.substring(5, 7)))
            ) {
                calendar.gotoDate(dateDebut);
            }
        },
        // ++++++++++++++++++++++++++++++AJOUT++++++++++++++++++++++++++++++++++++
        selectAllow: function (selectInfo) {
            // return selectInfo.start >= today;
            const day = selectInfo.start.getDay();
            // Vérifiez si la date est avant aujourd'hui ou un week-end
            return selectInfo.start >= today && day !== 0 && day !== 6;
        },

        // ++++++++++++++++++++++++++++++AJOUT++++++++++++++++++++++++++++++++++++

        eventDrop: function (info) {
            let myEvents = JSON.parse(localStorage.getItem("events")) || [];
            const eventIndex = myEvents.findIndex(
                (event) => event.id === info.event.id
            );
            const updatedEvent = {
                ...myEvents[eventIndex],
                id: info.event.id,
                title: info.event.title,
                start: moment(info.event.start).format("YYYY-MM-DD"),
            };
            myEvents.splice(eventIndex, 1, updatedEvent);
            localStorage.setItem("events", JSON.stringify(myEvents));
        },
    });

    // ********************* Calendar Select  *********************

    // Fonction pour formater la date
    const formatDate = (date) => {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, "0");
        const day = String(date.getDate()).padStart(2, "0");
        return `${year}-${month}-${day}`;
    };

    // Fonction pour vérifier si la date est non cliquable
    const isNonClickableDate = (date, nonClickableDates) => {
        const formattedDate = formatDate(date);
        return nonClickableDates.includes(formattedDate);
    };

    // Fonction pour gérer l'appel à l'API et afficher les modaux
    const handleDateSelection = async (info, action, myModal) => {
        const selectedDate = info.start;
        

        // Charger les données du fichier JSON
        fetch('/js/ferie.json')
            .then(response => response.json())
            .then(data => {
                const nonClickableDates = data.nonClickableDates;
                console.log("jours ferier:", nonClickableDates);
                // Vous pouvez maintenant utiliser `nonClickableDates` dans votre script
            })
            .catch(error => console.error('Erreur lors du chargement du JSON:', error));


        // const fs = require('fs');

        // const ferie = JSON.parset(fs.readFileSync('ferie.json'));
        // const nonClickableDates = ferie.nonClickableDates;


        const dayOfWeek = selectedDate.getDay();

        if (isNonClickableDate(selectedDate, nonClickableDates)) {
            return;
        }

        const formatedDate = formatDate(selectedDate);

        try {
            const response = await fetch(
                action.replace("modeLivraison", "verifDate"),
                {
                    method: "POST",
                    body: JSON.stringify({date: formatedDate}),
                    headers: {
                        "Content-Type": "application/json",
                    },
                }
            );

            if (!response.ok) {
                throw new Error("Une erreur s'est produite lors de la requête.");
            }

            const data = await response.json();

            if (data.message === "oui" && dayOfWeek >= 1 && dayOfWeek <= 5) {
                const startDateInput = document.getElementById("start-date");
                startDateInput.value = formatedDate;
                myModal.show();
            } else if (data.message === "non") {
                const [idPlein, titlePlein, contentPlein] = [
                    "feno",
                    "",
                    "Le jour sélectionné n'est plus disponible",
                ];
                generateModal(idPlein, titlePlein, contentPlein);
            } else {
                const [idWeekendPC, titleWeekendPC, contentWeekendPC] = [
                    "weekend",
                    "",
                    "Le jour sélectionné n'est pas un jour ouvrable (Lundi à Vendredi).",
                ];
                generateModal(idWeekendPC, titleWeekendPC, contentWeekendPC);
                return;
            }
        } catch (error) {
            console.error("Erreur :", error);
        }
    };

    // Utilisation de la fonction handleDateSelection dans l'événement 'select'
    calendar.on("select", (info) => {
        handleDateSelection(info, action, myModal);
    });

    // ********************* Calendar Select  *********************
    // ********************* DatePicker Select  *********************

    const datePicker = document.getElementById("start-date-mobile");

    datePicker.addEventListener("change", (event) => {
        // document.querySelector(".modal-backdrop").classList.add("d-none");
        document.querySelector("#form").classList.add("d-none");
        const selectedDate = new Date(event.target.value);
        const year = selectedDate.getFullYear();
        const month = String(selectedDate.getMonth() + 1).padStart(2, "0");
        const day = String(selectedDate.getDate()).padStart(2, "0");
        const formattedDate = `${year}-${month}-${day}`;
        datecom = formattedDate;
        const dayOfWeek = selectedDate.getDay();
        

        // Sélectionner tous les éléments avec la classe .fc-day
        const allDates = document.querySelectorAll('.fc-day');

        // Parcourir chaque élément
        allDates.forEach(dateElement => {
            // Récupérer la date de l'élément
            const date = dateElement.getAttribute('data-date');
            // Vérifier si la date est présente dans le tableau nonClickableDates
            if (nonClickableDates.includes(date)) {
                // Ajouter la classe .fc-ferie à l'élément
                dateElement.classList.add('fc-ferie');
            }
        });

        if (selectedDate < today) {
            document.getElementById("start-date-mobile").value = ""; // Réinitialiser la valeur du datepicker
            return;
        }

        if (selectedDate.getDay() === 6 || selectedDate.getDay() === 0) {
            document.getElementById("start-date-mobile").value = ""; // Réinitialiser la valeur du datepicker
            return;
        }

        if (nonClickableDates.includes(formattedDate)) {
            const [idFerie, titleFerie, contentFerie] = [
                "ferie",
                "Message",
                "Jour férié",
            ];
            generateModal(idFerie, titleFerie, contentFerie);
            document.getElementById("start-date-mobile").value = "";
            return;
        }
        fetch(action.replace("modeLivraison", "verifDate"), {
            method: "POST",
            body: JSON.stringify({
                date: formattedDate,
            }),
            headers: {
                "Content-Type": "application/json",
            },
        })
            .then((response) => {
                if (!response.ok) {
                    throw new Error("Une erreur s'est produite lors de la requête.");
                }
                return response.json();
            })
            .then((data) => {
                if (data.message === "oui" && dayOfWeek >= 1 && dayOfWeek <= 5) {
                    const startDateInput = document.getElementById("start-date-mobile");
                    startDateInput.value = formattedDate;
                } else if (data.message === "non") {
                    const [idPlein, titlePlein, contentPlein] = [
                        "feno",
                        "",
                        "Le jour sélectionné n'est plus disponible",
                    ];
                    generateModal(idPlein, titlePlein, contentPlein);

                    document.querySelector("#start-date-mobile").value = "";
                } else {
                    const [idWeekendPC, titleWeekendPC, contentWeekendPC] = [
                        "weekend",
                        "",
                        "Le jour sélectionné n'est pas un jour ouvrable (Lundi à Vendredi).",
                    ];
                    generateModal(idWeekendPC, titleWeekendPC, contentWeekendPC);
                    return;
                }
            })
            .catch((error) => {
                console.error("Une erreur s'est produite:", error);
            });
    });

    calendar.render();

    let form;
    if (isAndroid || isIOS) {
        form = document.getElementById("myFormMobile");
        console.log("android");
    } else {
        form = document.getElementById("myForm");
        console.log("pc");
    }

    const action = form.getAttribute("action");

    // taloha
    form.addEventListener("submit", function (event) {
        event.preventDefault();

        // var currentDate = new Date();
        // document.getElementById("dateRdv").value = currentDate;
        // console.log("date androany:"+currentDate);

        let titleId;
        if (isAndroid || isIOS) {
            titleId = "#event-title-mobile";
        } else {
            titleId = "#event-title";
        }
        let title = form.querySelector(titleId).value;
        if (title === 'AM') {
            title = '9h00 à 11h30';
        } else if (title === 'PM') {
            title = '14h00 à 16h30';
        }

        let startDateId;
        if (isAndroid || isIOS) {
            startDateId = "#start-date-mobile";
        } else {
            startDateId = "#start-date";
        }
        const startDate = form.querySelector(startDateId).value;
        const eventId = uuidv4();
        const formData = new FormData(form);
        formData.append("eventId", eventId); // Ajouter l'ID de l'événement au formulaire

        fetch(action, {
            method: "POST",
            body: formData,
        })
            .then((response) => {
                if (response.status === 401) {
                    myModal.hide();
                    const idPartie = "partie";
                    const titlePartie = "";
                    console.log(
                        "Vous ne pouvez plus commander pour cette partie de la journée "
                    );
                    const contentPartie =
                        "Vous ne pouvez plus commander pour cette partie de la journée ";

                    generateModal(idPartie, titlePartie, contentPartie);
                } else {
                    const confirmationModal = new bootstrap.Modal(
                        document.getElementById("confirmationModal")
                    );
                    document.querySelector("#confirmationModal .modal-body").innerHTML = `
                <p><strong>Votre rendez-vous pour la récupération de votre pièce :</strong></p>
                <p>Date : <strong>${startDate}</strong></p>
                <p>Plage horaire : <strong>${title}</strong></p>
            `;
                    myModal.hide();
                    confirmationModal.show();

                    const confirmButton = document.getElementById("confirmButton");
                    confirmButton.addEventListener("click", async function () {

                        formData.append('insert', 'ok');
                        try {

                            const spinner = document.getElementById('spinnerLivraison');
                            const confirmButtonColor = document.getElementById('confirmButton');
                            const buttonSpan = document.getElementById('buttonSpan');
                            spinner.classList.remove("d-none");
                            confirmButtonColor.style.backgroundColor = "#fff";
                            buttonSpan.innerHTML = "";
                            const response = await fetch(action, {
                                method: "POST",
                                body: formData,
                            });
                            spinner.classList.add("d-none");
                            buttonSpan.innerHTML = "Confirmer";
                            confirmButtonColor.style.backgroundColor = "#fff";
                            console.log('ok');
                            let now = window.location.href;
                            window.location.href = now;
                        } catch (error) {
                            console.error("Erreur :", error);
                        } finally {
                            // Masquer le loader une fois que la réponse est reçue ou qu'une erreur survient
                            loader.style.display = "none";
                        }

                        // Ajouter l'événement à l'interface utilisateur
                        const newEvent = {
                            id: eventId,
                            title: title,
                            start: startDate,
                        };
                        myEvents.push(newEvent);
                        calendar.addEvent(newEvent);
                        calendarEl.style.display = "none";

                        confirmationModal.hide();
                        form.reset();
                        calendar.refetchEvents();
                    });
                }
            })
            .catch((error) => {
                    console.error("Erreur :", error);
                }
            );
    });

    myModal._element.addEventListener("hide.bs.modal", function () {
        dangerAlert.style.display = "none";
        form.reset();
    });

    close.addEventListener("click", function () {
        const modal = document.querySelector(".modal");
        if (modal) {
            modal.classList.remove("show");
            document.querySelectorAll(".modal-backdrop").forEach(backdrop, () => {
                backdrop.style.display = "none";
            });
        }
    });

    ferme.addEventListener("click", function () {
        console.log("ferme");
        const confirmModal = document.getElementById("confirmationModal");
        if (confirmModal) {
            confirmModal.classList.remove("show");
            document.querySelectorAll(".modal-backdrop").forEach(backdrop => {
                backdrop.style.display = "none";
            });
        }
    });

    annule.addEventListener("click", function () {
        console.log("annule");
        const confirmModal = document.getElementById("confirmationModal");
        if (confirmModal) {
            confirmModal.classList.remove("show");
            document.querySelectorAll(".modal-backdrop").forEach(backdrop => {
                backdrop.style.display = "none";
            });
        }
    });
});
