/* global ajax, upload */

var controlPanelProfile = new ControlPanelProfile();

function ControlPanelProfile() {
    // Vars
    var self = this;
    
    // Properties
    
    // Functions public
    self.init = function() {
        upload.processFile("single");
        
        $("#form_cp_profile").on("submit", "", function(event) {
            event.preventDefault();
            
            ajax.send(
                true,
                false,
                $(this).prop("action"),
                $(this).prop("method"),
                $(this).serialize(),
                "json",
                false,
                null,
                function(xhr) {
                    ajax.reply(xhr, "#" + event.currentTarget.id);
                    
                    if (xhr.response.action !== undefined && xhr.response.action.refresh === true) {
                        popupEasy.create(
                            window.text.warning,
                            window.text.changeReload,
                            function() {
                                popupEasy.close();
                            },
                            null
                        );
                    }
                    
                    $("#popup_easy").on("hidden.bs.modal", "", function() {
                        $(".logout_button").click();
                    });
                },
                null,
                null
            );
        });

        $("#form_cp_profile_password").on("submit", "", function(event) {
            event.preventDefault();
            
            ajax.send(
                true,
                false,
                $(this).prop("action"),
                $(this).prop("method"),
                $(this).serialize(),
                "json",
                false,
                null,
                function(xhr) {
                    ajax.reply(xhr, "#" + event.currentTarget.id);
                },
                null,
                null
            );
        });
        
        $("#form_cp_profile_credits").on("submit", "", function(event) {
            event.preventDefault();
            
            ajax.send(
                true,
                false,
                $(this).prop("action"),
                $(this).prop("method"),
                $(this).serialize(),
                "json",
                false,
                null,
                function(xhr) {
                    ajax.reply(xhr, "#" + event.currentTarget.id);
                    
                    if (xhr.response.errors === undefined) {
                        var credits = $("#form_cp_profile_credits").find("input[name='credits']").val();
                        $("#form_cp_profile_credits_paypal").find("input[name='quantity']").val(credits);
                        
                        $("#form_cp_profile_credits_paypal").submit();
                    }
                },
                null,
                null
            );
        });
    };
    
    // Function private
}