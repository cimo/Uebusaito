/* global ajax, captcha */

var authentication = new Authentication();

function Authentication() {
    // Vars
    var self = this;
    
    // Properties
    
    // Functions public
    self.init = function() {
        $("#form_authentication").on("submit", "", function(event) {
            event.preventDefault();
            
            ajax.send(
                true,
                $(this).prop("action"),
                $(this).prop("method"),
                $(this).serialize(),
                "json",
                false,
                null,
                function(xhr) {
                    if (xhr.response.messages !== undefined) {
                        ajax.reply(xhr, "#" + event.currentTarget.id);
                        
                        if (xhr.response.values !== undefined && xhr.response.values.captchaReload === true)
                            captcha.image();
                    }
                    else
                        window.location.href = xhr.response.values.url;
                },
                null,
                null
            );
        });
        
        $("#user_logout").on("click", "", function(event) {
            event.preventDefault();
            
            ajax.send(
                true,
                window.url.authenticationExitCheck,
                "post",
                {
                    'event': "logout",
                    'token': window.session.token
                },
                "json",
                false,
                null,
                function(xhr) {
                    window.location.href = xhr.response.values.url;
                },
                null,
                null
            );
        });
    };
    
    // Function private
}