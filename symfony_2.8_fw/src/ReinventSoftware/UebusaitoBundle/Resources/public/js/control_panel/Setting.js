/* global ajax, utility, popupEasy */

var controlPanelSetting = new ControlPanelSetting();

function ControlPanelSetting() {
    // Vars
    var self = this;
    
    // Properties
    
    // Functions public
    self.init = function() {
        utility.wordTag("#form_settings_roleId");
        
        $("#form_settings_payPalCurrencyCode").on("keyup", "", function() {
            $(this).val($(this).val().toUpperCase());
        });
        
        $("#form_cp_settings").on("submit", "", function(event) {
            event.preventDefault();
            
            ajax.send(
                $(this).prop("action"),
                $(this).prop("method"),
                $(this).serialize(),
                true,
                null,
                function(xhr) {
                    ajax.reply(xhr, "#" + event.currentTarget.id);
                    
                    if (xhr.response.action !== undefined && xhr.response.action.refresh === true) {
                        popupEasy.create(
                            window.text.warning,
                            window.text.changeSettingReload,
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
    };
    
    // Function private
}