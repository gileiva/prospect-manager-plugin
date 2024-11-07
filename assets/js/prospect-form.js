jQuery(document).ready(function($) {
    $('#prospect-form').on('submit', function(e) {
        e.preventDefault();

        console.log('Formulario enviado, preparando datos...');

        var formData = $(this).serialize();
        formData += '&nonce=' + prospectFormAjax.nonce;

        $.ajax({
            url: prospectFormAjax.ajax_url,
            type: 'POST',
            data: formData + '&action=submit_prospect_form',
            success: function(response) {
                if (response.success) {
                    $('#prospect-form').html('<div class="form-success">' + response.data + '</div>');
                } else {
                    alert(response.data);
                }
            },
            error: function() {
                alert('There was an error. Please try again.');
            }
        });
    });

    $(".main-button").on("click", function() {
        $(".button-wrapper").addClass("clicked").delay(900).queue(function(next) {
            $(".layered-content").addClass("active");
            next();
        });
    });

    $(".close-button").on("click", function() {
        $(".layered-content").removeClass("active");
        $(".button-wrapper").removeClass("clicked");
    });
});
