$(document).ready(function () {

    var ias = jQuery.ias({
        container: '.timeline .box-content',
        item: '.publication-item',
        pagination: '.timeline.pagination',
        next: '.timeline .pagination .next_link',
        triggerPageThreshold: 5
    });

    ias.extension(new IASTriggerExtension({
        text: 'Ver mas publicaciones',
        offset: 3
    }));

    ias.extension(new IASSpinnerExtension({
        src: URL + '/../assets/media/loader.gif'
    }));

    ias.extension(new IASNoneLeftExtension({
        text: 'No hay mas publicaciones'
    }));

    ias.on('ready', function (event) {
        followButtons();
    });

    ias.on('rendered', function (event) {
        followButtons();
    });

});

function copyToClipboard(text) {
    var dummy = document.createElement("textarea");
    // to avoid breaking orgain page when copying more words
    // cant copy when adding below this code
    // dummy.style.display = 'none'
    document.body.appendChild(dummy);
    //Be careful if you use texarea. setAttribute('value', value), which works with "input" does not work with "textarea". – Eduard
    dummy.value = text;
    dummy.select();
    document.execCommand("copy");
    document.body.removeChild(dummy);
}

function errorAlert() {
    Swal.fire({
        icon: 'error',
        title: 'Ha ocurrido un problema',
        text: 'Esta opción aún está en desarrollo'
    })
}

function followButtons() {
    $(".btn-pub-option").unbind('click').click(function () {
        $(this).parent().find('.btn-delete-pub').focus();
    });
    
    $(".btn-edit").unbind('click').click(function () {
        errorAlert();
    });
    
    $(".btn-share").unbind('click').click(function () {
        copyToClipboard(URL + '/publication/stats/' + $(this).attr("data-id"));
        const Toast = Swal.mixin({
            toast: true,
            position: 'center',
            showConfirmButton: false,
            timer: 2500,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer)
                toast.addEventListener('mouseleave', Swal.resumeTimer)
            }
        })

        Toast.fire({
            icon: 'success',
            title: 'Se ha copiado el link al portapapeles!'
        })
    });

    $(".btn-delete-pub").unbind('click').click(function () {
        Swal.fire({
            title: '¿Seguro que desea borrar la publicación? ',
            text: "Esta acción no se puede deshacer",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Borrar'
        }).then((result) => {
            if (result.isConfirmed) {
                const Toast = Swal.mixin({
                    toast: true,
                    position: 'bottom',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true,
                    didOpen: (toast) => {
                        toast.addEventListener('mouseenter', Swal.stopTimer)
                        toast.addEventListener('mouseleave', Swal.resumeTimer)
                    }
                })

                Toast.fire({
                    icon: 'success',
                    title: 'Se ha eliminado la publicación'
                })
                $(this).parent().parent().parent().parent().parent().parent().attr("hidden", true);

                $.ajax({
                    url: URL + '/publication/remove/' + $(this).parent().attr("data-id"),
                    type: 'GET',
                    success: function (response) {
                        console.log("Se eliminó la publicación manito!")
                    }
                });
            }
        })

    });

    $(".btn-delete-pub-profile").unbind('click').click(function () {
        Swal.fire({
            title: '¿Seguro que desea borrar la publicación? ',
            text: "Esta acción no se puede deshacer",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Borrar'
        }).then((result) => {
            if (result.isConfirmed) {
                const Toast = Swal.mixin({
                    toast: true,
                    position: 'bottom',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true,
                    didOpen: (toast) => {
                        toast.addEventListener('mouseenter', Swal.stopTimer)
                        toast.addEventListener('mouseleave', Swal.resumeTimer)
                    }
                })

                Toast.fire({
                    icon: 'success',
                    title: 'Se ha eliminado la publicación'
                })
                $(this).parent().parent().parent().parent().attr("hidden", true);

                $.ajax({
                    url: URL + '/publication/remove/' + $(this).attr("data-id"),
                    type: 'GET',
                    success: function (response) {
                        console.log("Se eliminó la publicación manito!")
                    }
                });
            }
        })

    });

    $(".btn-like").unbind('click').click(function () {
        $(this).attr("hidden", true);
        $(this).parent().find('.btn-dislike').removeAttr("hidden");

        $.ajax({
            url: URL + '/like/' + $(this).attr("data-id"),
            type: 'GET',
            success: function (response) {
                console.log('Funcionó!!')
            }
        });
    });

    $(".btn-dislike").unbind('click').click(function () {
        $(this).attr("hidden", true);
        $(this).parent().find('.btn-like').removeAttr("hidden");

        $.ajax({
            url: URL + '/unlike/' + $(this).attr("data-id"),
            type: 'GET',
            success: function (response) {
                console.log('Funcionó!!')
            }
        });
    });

    $(".btn-follow").unbind("click").click(function () {
        $(this).attr("hidden", true);
        $(this).parent().find(".btn-unfollow").removeAttr("hidden");

        $.ajax({
            url: URL + '/follow',
            type: 'POST',
            data: {followed: $(this).attr("data-followed")},
            success: function (response) {
                console.log(response);
            }

        });
    });

    $(".btn-unfollow").unbind("click").click(function () {
        $(this).attr("hidden", true);
        $(this).parent().find(".btn-follow").removeAttr("hidden");

        $.ajax({
            url: URL + '/unfollow',
            type: 'POST',
            data: {followed: $(this).attr("data-followed")},
            success: function (response) {
                console.log(response);
            }

        });
    });
}