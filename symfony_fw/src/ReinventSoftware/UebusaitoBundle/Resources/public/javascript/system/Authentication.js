/* global ajax, captcha */

var authentication = new Authentication();

function Authentication() {
    // Vars
    var self = this;
    
    // Properties
    
    // Functions public
    self.init = function() {
        captcha.reload();
        
        $(".form_user_authentication input[name='_remember_me']").parents(".checkbox").addClass("remember_me_fix");
        
        $(".button_authentication_desktop").find("i").on("click", "", function() {
            if ($(this).parent().next().css("display") === "none")
                $(this).parent().next().show();
            else
                $(this).parent().next().hide();
        });
        
        $(".form_user_authentication").on("submit", "", function(event) {
            event.preventDefault();
            
            ajax.send(
                true,
                true,
                $(this).prop("action"),
                $(this).prop("method"),
                $(this).serialize(),
                "json",
                false,
                null,
                function(xhr) {
                    $("#authentication_button_mobile").dropdown("toggle");
                    
                    if (xhr.response.messages !== undefined) {
                        ajax.reply(xhr, "");
                        
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