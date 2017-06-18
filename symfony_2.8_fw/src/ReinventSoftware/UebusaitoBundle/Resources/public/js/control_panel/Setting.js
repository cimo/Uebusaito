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
                true,
                true,
                $(this).prop("action"),
                $(this).prop("method"),
                $(this).serialize(),
                "json",
                false,
                null,
                function(xhr) {
                    if (xhr.response.session !== undefined && xhr.response.session.userActivity !== "") {
                        ajax.reply(xhr, "");

                        return;
                    }
                    
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
        var index = 1;
        var code = "";
        
        $("#form_settings_language").on("change", "", function() {
            index = $(this).prop("selectedIndex");
            code = $("#form_settings_language").find("option").eq(index).val();
            
            if (index > 2)
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
                window.textSetting.deleteLanguageManage,
                function() {
                    popupEasy.close();
                    
                    ajax.send(
                        true,
                        true,
                        window.url.cpSettingsLanguageManage,
                        "post",
                        {
                            'event': "deleteLanguage",
                            'code': code,
                            'token': window.session.token
                        },
                        "json",
                        false,
                        null,
                        function(xhr) {
                            if (xhr.response.session !== undefined && xhr.response.session.userActivity !== "") {
                                ajax.reply(xhr, "");

                                return;
                            }

                            ajax.reply(xhr, "");
                            
                            if (xhr.response.messages.success !== undefined) {
                                $("#settings_language_manage_minus").removeClass("button_icon_inline");
                                $("#settings_language_manage_container").hide();
                                
                                $("#form_settings_language").find("option").eq(index).remove();
                                $("#form_language_codeText").find("option").eq(index).remove();
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
                true,
                true,
                window.url.cpSettingsLanguageManage,
                "post",
                {
                    'event': "createLanguage",
                    'code': code,
                    'token': window.session.token
                },
                "json",
                false,
                null,
                function(xhr) {
                    if (xhr.response.session !== undefined && xhr.response.session.userActivity !== "") {
                        ajax.reply(xhr, "");

                        return;
                    }
                    
                    ajax.reply(xhr, "");
                    
                    if (xhr.response.messages.success !== undefined) {
                        $("#form_settings_language").append("<option value=\"" + code + "\">" + code + "</option>");
                        $("#form_language_codeText").append("<option value=\"" + code + "\">" + code + "</option>");
                        
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