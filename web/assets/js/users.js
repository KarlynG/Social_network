$(document).ready(function () {

    var ias = jQuery.ias({
        container: '.box-users',
        item: '.user-item',
        pagination: '.pagination',
        next: '.pagination .next_link',
        triggerPageThreshold: 5
    });

    ias.extension(new IASTriggerExtension({
        text: 'Ver mas',
        offset: 3
    }));

    ias.extension(new IASSpinnerExtension({
        src: URL + '/../assets/media/loader.gif'
    }));

    ias.extension(new IASNoneLeftExtension({
        text: 'No se encontraron más personas :('
    }));

    ias.on('ready', function (event) {
        followButtons();
    });

    ias.on('rendered', function (event) {
        followButtons();
    });

});

function followButtons() {
    $(".btn-follow").unbind("click").click(function () {
        $(this).attr("hidden",true);
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
        $(this).attr("hidden",true);
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