/**
 * Sweet Alert 2
 */
function flashAlert(icon, title) {
    Swal.fire({
        toast: true,
        icon: icon,
        title: title,
        position: 'top-end',
        showConfirmButton: false,
        timer: 5000,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer)
            toast.addEventListener('mouseleave', Swal.resumeTimer)
        }
    });
}

function modalAlert(title, icon) {
    Swal.fire({
        icon: icon,
        title: title,
        showCancelButton: false,
        confirmButtonColor: '#9f662e',
        confirmButtonText: 'Accepter',
        allowOutsideClick: false,
        allowEscapeKey: false,
    });

    if (!icon) {
        $('button.swal2-confirm').prop('disabled', true);
    }
}

function modalOnSubmit(form, type = 1) {
    Swal.fire({
        title: 'Etes-vous sûr ?',
        text: "Cette action est irréversible !",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: type === 1 ? '#dc3545' : (type === 2 ? '#198754' : '#ffc107'),
        cancelButtonColor: '#51585e',
        confirmButtonText: type === 1 ? 'Supprimer' : (type === 2 ? 'Regénérer' : 'Verrouiller'),
        cancelButtonText: 'Annuler',
        allowOutsideClick: false,
        allowEscapeKey: false,
    }).then((result) => {
        if (result.isConfirmed) {
            form.submit();
        }
    });
}
