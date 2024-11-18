function launchAjax(urlValidate, urlRedirect) {
    $("#recupererValeurs").on("click", function (event) {
        let checkboxes = $(".checkbox");
        let valeursCochees = [];
        checkboxes.each(function () {
            if ($(this).prop("checked")) {
                valeursCochees.push($(this).val());
            }
        });
        if (valeursCochees.length > 0) {
            event.preventDefault();
            $.ajax({
                url: urlValidate,
                type: "POST",
                contentType: "application/json",
                data: JSON.stringify(valeursCochees),
                success: function (response) {
                    if (response.isSuccess) {
                        Notiflix.Report.Success('Validation effectuée', '', 'ok', function () {
                            location.replace(urlRedirect);
                        });
                    }
                },
                error: function (error) {
                    Notiflix.Report.Failure('Une erreur s\'est produite', '', 'ok', function () {
                        location.replace(urlRedirect);
                    });
                }
            });
        }
    });
}
