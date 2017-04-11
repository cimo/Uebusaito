/* global ajax, utility, popupEasy */

var controlPanelSetting = new ControlPanelSetting();

function ControlPanelSetting() {
    // Vars
    var self = this;
    
    // Properties
    
    // Functions public
    self.init = function() {
        utility.accordion();
        
        languageManage();
        
        utility.wordTag("#form_settings_roleId");
        
        $("#form_settings_payPalCurrencyCode").on("keyup", "", function() {
            $(this).val($(this).val().toUpperCase());
        });
        
        $("#form_cp_settings_modify").on("submit", "", function(event) {
            event.preventDefault();
            
            var propNameOld = $("#form_settings_languageManage").prop("name");
            $("#form_settings_languageManage").removeAttr("name");
            
            $("#settings_language_manage_minus").removeClass("button_icon_inline");
            $("#settings_language_manage_container").hide();
            
            ajax.send(
                $(this).prop("action"),
                $(this).prop("method"),
                $(this).serialize(),
                true,
                null,
                function(xhr) {
                    ajax.reply(xhr, "#" + event.currentTarget.id);
                    
                    $("#form_settings_languageManage").prop("name", propNameOld);
                    
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
    };
    
    // Function private
    function languageManage() {
        var currentIndex = 1;
        
        $("#form_settings_language").on("change", "", function() {
            currentIndex = $(this).prop("selectedIndex");
            
            if (currentIndex > 2)
                $("#settings_language_manage_minus").addClass("button_icon_inline");
            else
                $("#settings_language_manage_minus").removeClass("button_icon_inline");
        });
        
        $("#settings_language_manage_plus").on("click", "", function() {
            $("#settings_language_manage_container").show();
        });
        
        $("#settings_language_manage_minus").on("click", "", function() {
            popupEasy.create(
                window.text.warning,
                window.text.deleteLanguageManage,
                function() {
                    popupEasy.close();
                    
                    ajax.send(
                        window.url.cpSettingsLanguageManage,
                        "post",
                        {
                            'event': "deleteLanguage",
                            'currentIndex': currentIndex,
                            'token': window.session.token
                        },
                        true,
                        null,
                        function(xhr) {
                            ajax.reply(xhr, "");
                            
                            if (xhr.response.messages.success !== undefined) {
                                $("#settings_language_manage_minus").removeClass("button_icon_inline");
                                $("#settings_language_manage_container").hide();
                                
                                $("#form_settings_language").find("option").eq(currentIndex).remove();
                            }
                        },
                        null,
                        null
                    );
                },
                function() {
                    popupEasy.close();
                }
            );
        });
        
        $("#settings_language_manage_confirm").on("click", "", function() {
            var code = $("#form_settings_languageManage").val();
            
            ajax.send(
                window.url.cpSettingsLanguageManage,
                "post",
                {
                    'event': "createLanguage",
                    'code': code,
                    'token': window.session.token
                },
                true,
                null,
                function(xhr) {
                    ajax.reply(xhr, "");
                    
                    if (xhr.response.messages.success !== undefined) {
                        $("#form_settings_language").append("<option value=\"" + code + "\">" + code + "</option>");
                        
                        $("#settings_language_manage_erase").click();
                    }
                },
                null,
                null
            );
        });
        
        $("#settings_language_manage_erase").on("click", "", function() {
            $("#form_settings_languageManage").val("");
            $("#settings_language_manage_container").hide();
        });
    }
}