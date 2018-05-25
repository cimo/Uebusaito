/* global utility, ajax, upload, popupEasy */

var controlPanelProfile = new ControlPanelProfile();

function ControlPanelProfile() {
    // Vars
    var self = this;
    
    // Properties
    
    // Functions public
    self.init = function() {
        upload.init();
        upload.processFile();
        upload.setTagImageRefresh(".img-thumbnail.avatar", 1);
        
        $("#form_cp_profile").on("submit", "", function(event) {
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
                    ajax.reply(xhr, "#" + event.currentTarget.id);
                },
                null,
                null
            );
        });

        $("#form_cp_profile_password").on("submit", "", function(event) {
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
                    ajax.reply(xhr, "#" + event.currentTarget.id);
                },
                null,
                null
            );
        });
        
        $("#form_cp_profile_credit").on("submit", "", function(event) {
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
                    ajax.reply(xhr, "#" + event.currentTarget.id);
                    
                    if (xhr.response.errors === undefined) {
                        var credit = $("#form_cp_profile_credit").find("input[name='credit']").val();
                        $("#form_cp_profile_credit_paypal").find("input[name='quantity']").val(credit);
                        
                        $("#form_cp_profile_credit_paypal").submit();
                    }
                },
                null,
                null
            );
        });
    };
    
    // Function private
}