/* global utility, ajax, upload */

var myPageProfile = new MyPageProfile();

function MyPageProfile() {
    // Vars
    var self = this;
    
    // Properties
    
    // Functions public
    self.init = function() {
        upload.init();
        upload.setUrlRequest(window.url.myPageProfileUpload);
        upload.setTagContainer("#upload_myPage_profile_container");
        upload.setTagProgressBar("#upload_myPage_profile_container .upload .mdc-linear-progress");
        upload.setTagImageRefresh("#upload_myPage_profile_container .avatar");
        upload.processFile();
        
        $("#form_myPage_profile").on("submit", "", function(event) {
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

        $("#form_myPage_profile_password").on("submit", "", function(event) {
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
        
        $("#form_myPage_profile_credit").on("submit", "", function(event) {
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
                        var credit = $("#form_myPage_profile_credit").find("input[name='credit']").val();
                        $("#form_myPage_profile_credit_paypal").find("input[name='quantity']").val(credit);
                        
                        $("#form_myPage_profile_credit_paypal").submit();
                    }
                },
                null,
                null
            );
        });
    };
    
    // Function private
}