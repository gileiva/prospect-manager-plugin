jQuery(document).ready(function($) {
    $('#add-tracking-message').on('click', function(e) {
        e.preventDefault();

        let postId = $('#post_ID').val();
        let newMessage = $('#new_tracking_message').val();

        if (newMessage.trim() === '') {
            alert('Please enter a message.');
            return;
        }

        $.ajax({
            url: trackingAjax.ajax_url,
            type: 'POST',
            data: {
                action: 'add_tracking_message',
                post_id: postId,
                message: newMessage,
                nonce: trackingAjax.nonce
            },
            success: function(response) {
                if (response.success) {
                    $('#tracking-messages').prepend('<p><strong>' + response.data.date + ':</strong> ' + response.data.content + '</p>');
                    $('#new_tracking_message').val(''); // Limpiar el campo de entrada
                } else {
                    alert('Error: ' + response.data);
                }
            },
            error: function() {
                alert('An error occurred while adding the message.');
            }
        });
    });
});
