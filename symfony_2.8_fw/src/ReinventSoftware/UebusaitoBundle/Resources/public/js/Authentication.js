/* global ajax */

var authentication = new Authentication();

function Authentication() {
    // Vars
    var self = this;
    
    // Properties
    
    // Functions public
    self.init = function() {
        $(".form_user_authentication input[name='_remember_me']").parents(".checkbox").addClass("remember_me_fix");
        
        $(".form_user_authentication").on("submit", "", function(event) {
            event.preventDefault();
            
            ajax.send(
                $(this).prop("action"),
                $(this).prop("method"),
                $(this).serialize(),
                true,
                null,
                function(xhr) {
                    if (xhr.response.messages !== undefined) {
                        ajax.reply(xhr, "");
                        
                        $("#authentication_button").dropdown("toggle");
                        
                        if ($("#menu_root_navbar").hasClass("in") === true)
                            $("#menu_root_nav_button").click();
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
                event.target.href,
                "post",
                {
                    'token': window.session.token
                },
                true,
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