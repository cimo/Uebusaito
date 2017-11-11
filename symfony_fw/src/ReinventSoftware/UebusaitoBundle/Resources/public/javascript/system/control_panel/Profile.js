/* global utility, ajax, upload, popupEasy */

var controlPanelProfile = new ControlPanelProfile();

function ControlPanelProfile() {
    // Vars
    var self = this;
    
    // Properties
    
    // Functions public
    self.init = function() {
        upload.processFile();
        upload.setTagImageRefresh(".img-thumbnail.avatar", 1);
        
        $("#cp_profile_form").on("submit", "", function(event) {
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
                    ajax.reply(xhr, "#" + event.currentTarget.id);
                },
                null,
                null
            );
        });

        $("#cp_profile_password_form").on("submit", "", function(event) {
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
                    ajax.reply(xhr, "#" + event.currentTarget.id);
                },
                null,
                null
            );
        });
        
        $("#cp_profile_credit_form").on("submit", "", function(event) {
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
                    ajax.reply(xhr, "#" + event.currentTarget.id);
                    
                    if (xhr.response.errors === undefined) {
                        var credit = $("#cp_profile_credit_form").find("input[name='credit']").val();
                        $("#cp_profile_credit_paypal_form").find("input[name='quantity']").val(credit);
                        
                        $("#cp_profile_credit_paypal_form").submit();
                    }
                },
                null,
                null
            );
        });
    };
    
    // Function private
}