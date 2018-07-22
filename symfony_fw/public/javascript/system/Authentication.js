/* global ajax, captcha */

var authentication = new Authentication();

function Authentication() {
    // Vars
    var self = this;
    
    // Properties
    
    // Functions public
    self.init = function() {
        $(".form_user_authentication").on("submit", "", function(event) {
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
        
        $(".logout_button").on("click", "", function(event) {
            event.preventDefault();
            
            ajax.send(
                true,
                event.target.href,
                "post",
                {
                    'event': "logout",
                    'token': window.session.token
                },
                "json",
                false,
                null,
                function(xhr) {
                    if (xhr.response.messages !== undefined)
                        ajax.reply(xhr, "");
                    else
                        window.location.href = xhr.response.values.url;
                },
                null,
                null
            );
        });
    };
    
    // Function private
}