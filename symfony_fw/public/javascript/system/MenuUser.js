var menuUser = new MenuUser();

function MenuUser() {
    // Vars
    var self = this;
    
    // Properties
    
    // Functions public
    self.init = function() {
        $("#menu_user").find(".control_panel").on("click", "", function() {
            window.location.href = window.url.controlPanel;
        });
        $("#menu_user").find(".myPage").on("click", "", function() {
            window.location.href = window.url.root + "/" + window.session.languageTextCode + "/1";
        });
        $("#menu_user").find(".logout").on("click", "", function() {
            window.location.href = window.url.authenticationExitCheck;
        });
        $("#menu_user").find(".login").on("click", "", function() {
            window.location.href = window.url.root + "/" + window.session.languageTextCode + "/0/user_login";
        });
    };
    
    // Functions private
}