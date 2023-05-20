$(document).ready(function() {
    $('#loginForm').submit(function(e) {
        e.preventDefault();

        var username = $('input[name="username"]').val();
        var password = $('input[name="password"]').val();

        $.ajax({
            type: 'POST',
            url: 'auth.php',
            data: { username: username, password: password },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    window.location.href = 'profile.php';
                } else {
                    $('#error').text(response.error);
                }
            }
        });
    });

    setTimeout(function() {
        $('.success-message').fadeOut('slow');
    }, 10000);
});
