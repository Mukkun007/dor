$(document).ready(function () {
    let civilitySelect = document.getElementById("user_civility"),
        maritalStatusSelect = document.getElementById("user_maritalStatus"),
        nationality = $('#user_nationality'),
        userForm = $('#userForm'),
        cin = $('.cin'),
        allCinHidden = $('.cin-hidden'),
        passport = $('.passport'),
        passportInput = $('#user_passport'),
        birthdayInput = document.getElementById("user_birthday"),
        passportExpInput = document.getElementById("user_passportExp"),
        country = $('.country'),
        account = $('#user_account'),
        affiliation = $('.affiliation'),
        rib = $('.rib'),
        allRibHidden = $('.all-rib-hidden'),
        iban = $('.iban'),
        ibanInput = $('#user_iban'),
        swiftInput = $('#user_swift'),
        user_cin = $('.user_cin'),
        user_rib = $('.user_rib'),
        fc1 = $('.fc-1'),
        tel = $('#user_phone'),
        numericKeys = '0123456789',
        alphanumeric = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789',
        isAndroid = platform.os.family === 'Android',
        isIOS = platform.os.family === 'iOS';

    displayBankInfo(getRibValue());

    userForm.validate({
        ignore: ":hidden:not(.cin-hidden):not(.all-rib-hidden), .user_cin, .user_rib",
        rules: {
            "user[email]": {
                required: true,
                email: true,
                customEmailValidation: true
            },
            "user[cin]": {
                minlength: 12
            },
            "user[rib]": {
                codeBankyValidator: true,
                ribValidator: true,
                minlength: 23
            },
            "user[cinFile]": {
                accept: "image/png,image/jpeg,application/pdf",
                maxsize: 2000000
            },
            "user[passportFile]": {
                accept: "image/png,image/jpeg,application/pdf",
                maxsize: 2000000
            },
            "user[ribFile]": {
                accept: "image/png,image/jpeg,application/pdf",
                maxsize: 2000000
            },
            "user[affiliationFile]": {
                accept: "image/png,image/jpeg,application/pdf",
                maxsize: 2000000
            },
            "user[ibanFile]": {
                accept: "image/png,image/jpeg,application/pdf",
                maxsize: 2000000
            }
        },
        messages: {
            "user[cin]": {
                minlength: "Ce champ est obligatoire."
            },
            "user[rib]": {
                minlength: "Ce champ est obligatoire."
            },
            "user[cinFile]": {
                accept: "Veuillez télécharger un png, jpeg, pdf",
                maxsize: "Veuillez télécharger un fichier inférieur à 2 Mo"
            },
            "user[passportFile]": {
                accept: "Veuillez télécharger un png, jpeg, pdf",
                maxsize: "Veuillez télécharger un fichier inférieur à 2 Mo"
            },
            "user[ribFile]": {
                accept: "Veuillez télécharger un png, jpeg, pdf",
                maxsize: "Veuillez télécharger un fichier inférieur à 2 Mo"
            },
            "user[affiliationFile]": {
                accept: "Veuillez télécharger un png, jpeg, pdf",
                maxsize: "Veuillez télécharger un fichier inférieur à 2 Mo"
            },
            "user[ibanFile]": {
                accept: "Veuillez télécharger un png, jpeg, pdf",
                maxsize: "Veuillez télécharger un fichier inférieur à 2 Mo"
            }
        },
        errorPlacement: function (error, element) {
            if (element.hasClass('customError')) {
                error.appendTo(element.parents('div.errorGroup'));
            } else {
                element.after(error);
            }
        }
    });
    $.validator.addMethod("customEmailValidation", function (value, element) {
        return this.optional(element) || /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value);
    }, "L'adresse email saisie doit contenir un @ et un .");
    $.validator.addMethod("codeBankyValidator", function () {
        if ($('.ribGroup').hasClass('d-none')) {
            return true;
        }

        let _rib_Value_ = getRibValue();
        switch (_rib_Value_) {
            case '00004':
            case '00005':
            case '00006':
            case '00007':
            case '00008':
            case '00009':
            case '00011':
            case '00012':
            case '00013':
            case '00014':
            case '00015':
            case '00016':
            case '00017':
            case '00997':
            case '00998':
            case '00999':
                return true;
            default:
                return false;
        }
    }, "Veuillez entrer un code banque valide.");
    $.validator.addMethod("ribValidator", function () {
        if ($('.ribGroup').hasClass('d-none')) {
            return true;
        }

        let bq = getAllRibValue().substring(0, 5);
        let ag = getAllRibValue().substring(5, 10);
        let cpte = getAllRibValue().substring(10, 21);
        let rib_saisie = getAllRibValue().substring(21, 23);

        return parseInt(rib_saisie) === 97 - (((parseInt(bq) % 97) * 89 + (parseInt(ag) % 97) * 15 + (Math.floor(parseInt(cpte) / 100000) % 97) * 76 + ((parseInt(cpte) % 100000) % 97) * 3) % 97);
    }, "Veuillez entrer un rib valide.");

    userForm.on('submit', function () {
        fillCin();
        fillRib();
    });

    nationality.on('change', function () {
        if (parseInt($(this).val()) === 1) {
            cin.addClass('d-none');
            cin.find('input').each(function () {
                $(this).prop('required', false);
            });
            passport.removeClass('d-none');
            passport.find('input').each(function () {
                $(this).prop('required', true);
            });
            country.removeClass('d-none');
            country.find('select').each(function () {
                $(this).prop('required', true);
            });
        } else {
            cin.removeClass('d-none');
            cin.find('input').each(function () {
                $(this).prop('required', true);
            });
            passport.addClass('d-none');
            passport.find('input').each(function () {
                $(this).prop('required', false);
            });
            country.addClass('d-none');
            country.find('select').each(function () {
                $(this).prop('required', false);
            });
        }
    });

    user_cin.on('keyup', function (event) {
        let allowedKeys = ['ArrowLeft', 'ArrowRight', 'Backspace', 'Tab'],
            currentIndex = user_cin.index(this),
            key = event.key;
        // Ajouter les touches numériques de '0' à '9'
        for (let i = 0; i <= 9; i++) {
            allowedKeys.push(i.toString());
        }
        allowedKeys.push('Shift+Tab'); // Ajouter Shift+Tab

        if (this.value.length === this.maxLength) {
            if (!allowedKeys.includes(event.key)) {
                return; // Ne rien faire si la touche pressée n'est pas dans allowedKeys
            }
            if (event.shiftKey && event.key === 'Tab') {
                event.preventDefault(); // Bloquer la commande Shift+Tab
                let prevField = $(this).prevAll('.user_cin:first');
                prevField.select().focus(); // Sélectionner et focus sur le champ précédent
                return; // Arrêter la propagation de l'événement
            } else if (event.key === 'Shift' && event.shiftKey) {
                event.preventDefault(); // Bloquer la commande Shift seule
                $(this).prevAll('.user_cin:first').select().focus(); // Sélectionner et focus sur le champ précédent
                return; // Arrêter la propagation de l'événement
            } else {
                $(this).nextAll('.user_cin:first').focus(); // Focus sur le champ suivant
            }
        }

        if (key === 'ArrowRight') {
            if (currentIndex < user_cin.length - 1) {
                user_cin.eq(currentIndex + 1).focus();
            }
        } else if (key === 'ArrowLeft') {
            if (currentIndex > 0) {
                user_cin.eq(currentIndex - 1).focus();
            }
        } else if (key === 'Backspace') {
            if (currentIndex > 0 && $(this).val().length === 0) {
                // Si la touche backspace est pressée et le champ est vide,
                // déplacer le focus vers l'input précédent et supprimer son contenu
                user_cin.eq(currentIndex - 1).focus().val('');
            }
        } else if (key === 'ArrowUp' || key === 'ArrowDown') {
            event.preventDefault(); // Bloquer les touches "up" et "down"
        }

        fillCin();
    });

    user_rib.on('keyup', function (event) {
        let allowedKeys = ['ArrowLeft', 'ArrowRight', 'Backspace', 'Tab'],
            currentIndex = user_rib.index(this),
            key = event.key;

        // Ajouter les touches numériques de '0' à '9'
        for (let i = 0; i <= 9; i++) {
            allowedKeys.push(i.toString());
        }
        allowedKeys.push('Shift+Tab'); // Ajouter Shift+Tab

        if (this.value.length === this.maxLength) {
            if (!allowedKeys.includes(event.key)) {
                return; // Ne rien faire si la touche pressée n'est pas dans allowedKeys
            }
            if (event.shiftKey && event.key === 'Tab') {
                event.preventDefault(); // Bloquer la commande Shift+Tab
                let prevField = $(this).prevAll('.user_rib:first');
                prevField.select().focus(); // Sélectionner et focus sur le champ précédent
                return; // Arrêter la propagation de l'événement
            } else if (event.key === 'Shift' && event.shiftKey) {
                event.preventDefault(); // Bloquer la commande Shift seule
                $(this).prevAll('.user_rib:first').select().focus(); // Sélectionner et focus sur le champ précédent
                return; // Arrêter la propagation de l'événement
            } else {
                $(this).nextAll('.user_rib:first').focus(); // Focus sur le champ suivant
            }
        }

        if (key === 'ArrowRight') {
            if (currentIndex < user_rib.length - 1) {
                user_rib.eq(currentIndex + 1).focus();
            }
        } else if (key === 'ArrowLeft') {
            if (currentIndex > 0) {
                user_rib.eq(currentIndex - 1).focus();
            }
        } else if (key === 'Backspace') {
            if (currentIndex > 0 && $(this).val().length === 0) {
                // Si la touche backspace est pressée et le champ est vide,
                // déplacer le focus vers l'input précédent et supprimer son contenu
                user_rib.eq(currentIndex - 1).focus().val('');
            }
        } else if (key === 'ArrowUp' || key === 'ArrowDown') {
            event.preventDefault(); // Bloquer les touches "up" et "down"
        }

        fillRib();
    });

    account.on('change', function () {
        if (parseInt($(this).val()) === 1) {
            affiliation.addClass('d-none');
            affiliation.find('input').each(function () {
                $(this).prop('required', false);
            });
            iban.addClass('d-none');
            iban.find('input').each(function () {
                $(this).prop('required', false);
            });
            rib.removeClass('d-none');
            rib.find('input').each(function () {
                $(this).prop('required', true);
            });
        } else if (parseInt($(this).val()) === 2) {
            rib.addClass('d-none');
            rib.find('input').each(function () {
                $(this).prop('required', false);
            });
            iban.addClass('d-none');
            iban.find('input').each(function () {
                $(this).prop('required', false);
            });
            affiliation.removeClass('d-none');
            affiliation.find('input').each(function () {
                $(this).prop('required', true);
            });
        } else {
            affiliation.addClass('d-none');
            affiliation.find('input').each(function () {
                $(this).prop('required', false);
            });
            rib.addClass('d-none');
            rib.find('input').each(function () {
                $(this).prop('required', false);
            });
            iban.removeClass('d-none');
            iban.find('input').each(function () {
                $(this).prop('required', true);
            });
        }
    });

    fc1.on('keypress', function (event) {
        if (event.charCode === 0) {
            return;
        }

        if (-1 === numericKeys.indexOf(event.key)) {
            event.preventDefault();
        }
    });

    tel.on('keypress', function (event) {
        if (event.charCode === 0) {
            return;
        }

        if (-1 === numericKeys.indexOf(event.key)) {
            event.preventDefault();
        }
    });

    passportInput.on('keypress', function (event) {
        if (-1 === alphanumeric.indexOf(event.key)) {
            event.preventDefault();
        }
    });

    ibanInput.on('keypress', function (event) {
        if (-1 === alphanumeric.indexOf(event.key)) {
            event.preventDefault();
        }
    });

    swiftInput.on('keypress', function (event) {
        if (-1 === alphanumeric.indexOf(event.key)) {
            event.preventDefault();
        }
    });

    // Ajoute un écouteur d'événements pour le changement de civilité
    civilitySelect.addEventListener("change", function () {
        // Si "Mlle" est sélectionné dans la civilité
        if (civilitySelect.value == 3) {
            // Sélectionne automatiquement "Célibataire" dans la situation familiale
            maritalStatusSelect.value = 2;
        } else {
            maritalStatusSelect.value = 1;
        }
    });

    console.log("type d'ecran ::", platform.os.family)

    //type date en type text en android
    if (isAndroid) {
        birthdayInput.type = "text";
        passportExpInput.type = "text";
        birthdayInput.placeholder = "jj/mm/aaaa";
        passportExpInput.placeholder = "jj/mm/aaaa";
        birthdayInput.pattern = "\\d{2}/\\d{2}/\\d{4}";
        passportExpInput.pattern = "\\d{2}/\\d{2}/\\d{4}";
    }

    //type date en type text en iOS
    if (isIOS) {
        birthdayInput.type = "text";
        passportExpInput.type = "text";
        birthdayInput.placeholder = "jj/mm/aaaa";
        passportExpInput.placeholder = "jj/mm/aaaa";
        birthdayInput.pattern = "\\d{2}/\\d{2}/\\d{4}";
        passportExpInput.pattern = "\\d{2}/\\d{2}/\\d{4}";
    }

    function fillCin() {
        allCinHidden.val(getCinValue()).valid();
        if (allCinHidden.hasClass('valid')) {
            user_cin.each(function () {
                if ($(this).hasClass('has-error')) {
                    $(this).removeClass('has-error');
                }
            });
        } else if (allCinHidden.hasClass('error')) {
            user_cin.each(function () {
                if (!$(this).hasClass('has-error')) {
                    $(this).addClass('has-error');
                }
            });
        }
    }

    function fillRib() {
        allRibHidden.val(getAllRibValue()).valid();
        if (allRibHidden.hasClass('valid')) {
            user_rib.each(function () {
                if ($(this).hasClass('has-error')) {
                    $(this).removeClass('has-error');
                }
            });
        } else if (allRibHidden.hasClass('error')) {
            user_rib.each(function () {
                if (!$(this).hasClass('has-error')) {
                    $(this).addClass('has-error');
                }
            });
        }
    }
});

function cgvConfirm() {
    if ($('.cgv-confirm').is(':checked')) {
        $('button.swal2-confirm').prop('disabled', false);
    } else {
        $('button.swal2-confirm').prop('disabled', true);
    }
}

$(document).ready(function () {
    const quantitySelect = document.getElementById('user_or_quantity');
    const totalPriceDisplay = document.getElementById('user_prix_total');
    const pricePerPiece = 12000000; // Prix par pièce d'or en Ar

    // Fonction pour mettre à jour le prix total
    function updateTotalPrice() {
        const selectedQuantity = parseInt(quantitySelect.value, 10);
        const totalPrice = selectedQuantity * pricePerPiece;
        totalPriceDisplay.textContent = totalPrice.toLocaleString(); // Formatage avec séparateurs de milliers
    }

    // Mise à jour initiale
    updateTotalPrice();

    // Met à jour lorsque l'utilisateur change la quantité
    quantitySelect.addEventListener('change', updateTotalPrice);
});
