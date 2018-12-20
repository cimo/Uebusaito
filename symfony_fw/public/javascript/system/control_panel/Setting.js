/* global ajax, utility, popupEasy, materialDesign */

var controlPanelSetting = new ControlPanelSetting();

function ControlPanelSetting() {
    // Vars
    var self = this;
    
    // Properties
    
    // Functions public
    self.init = function() {
        languageManage();
        
        utility.wordTag("#setting_roleUserId", "#form_setting_roleUserId");
        
        $("#form_setting_payPalCurrencyCode").on("keyup", "", function() {
            $(this).val($(this).val().toUpperCase());
        });
        
        $("#form_cp_setting_save").on("submit", "", function(event) {
            event.preventDefault();
            
            var propNameLanguageManageCode = $("#form_setting_languageManageCode").prop("name");
            $("#form_setting_languageManageCode").removeAttr("name");
            var propNameLanguageManageDate = $("#form_setting_languageManageDate").prop("name");
            $("#form_setting_languageManageDate").removeAttr("name");
            
            $("#setting_language_manage_delete").removeClass("button_icon_inline");
            $("#setting_language_manage_close").click();
            
            ajax.send(
                true,
                $(this).prop("action"),
                $(this).prop("method"),
                $(this).serialize(),
                "json",
                false,
                true,
                "application/x-www-form-urlencoded; charset=UTF-8",
                null,
                function(xhr) {
                    ajax.reply(xhr, "#" + event.currentTarget.id);
                    
                    $("#form_setting_languageManageCode").prop("name", propNameLanguageManageCode);
                    $("#form_setting_languageManageDate").prop("name", propNameLanguageManageDate);
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
        var eventAjax = "";
        
        if (index < 2)
            $("#setting_language_manage_delete").hide();
        
        $("#form_setting_language").on("change", "", function() {
            $("#setting_language_manage_close").click();
            
            index = $(this).prop("selectedIndex");
            code = $(this).val();
            
            if (index > 2)
                $("#setting_language_manage_delete").show();
            else
                $("#setting_language_manage_delete").hide();
        });
        
        $("#setting_language_manage_modify").on("click", "", function() {
            eventAjax = "modifyLanguage";
            
            $("#setting_language_manage_container").show();
            
            var valueCodeSelected = $("#form_setting_language").find(":selected").val();
            var valueDateSelected = $("#form_setting_language").find(":selected").text().replace(valueCodeSelected + " | ", "");
            
            $("#form_setting_languageManageCode").prop("disabled", true);
            $("#form_setting_languageManageCode").val(valueCodeSelected);
            $("#form_setting_languageManageDate").val(valueDateSelected);
            
            materialDesign.refresh();
        });
        
        $("#setting_language_manage_create").on("click", "", function() {
            eventAjax = "createLanguage";
            
            $("#setting_language_manage_container").show();
            
            $("#form_setting_languageManageCode").prop("disabled", false);
            $("#form_setting_languageManageCode").val("");
            $("#form_setting_languageManageDate").val("");
        });
        
        $("#setting_language_manage_confirm").on("click", "", function() {
            var code = $("#form_setting_languageManageCode").val();
            var date = $("#form_setting_languageManageDate").val();
            
            ajax.send(
                true,
                window.url.cpSettingLanguageManage,
                "post",
                {
                    'event': eventAjax,
                    'code': code,
                    'date': date,
                    'token': window.session.token
                },
                "json",
                false,
                true,
                "application/x-www-form-urlencoded; charset=UTF-8",
                null,
                function(xhr) {
                    ajax.reply(xhr, "");
                    
                    if (xhr.response.messages.success !== undefined) {
                        $("#form_setting_language").append("<option value=\"" + code + "\">" + code + "</option>");
                        $("#language_text_container").find(".mdc-menu__items.mdc-list").append(
                                "<li class=\"mdc-list-item\" role=\"menuitem\">\n\
                                    <img class=\"" + code + "\" src=\"" + window.url.root + "images/templates/" + window.setting.template + "/lang/" + code + ".png\" alt=\"" + code + ".png\"/>\n\
                                </li>"
                        );
                        
                        $("#setting_language_manage_close").click();
                    }
                },
                null,
                null
            );
        });
        
        $("#setting_language_manage_delete").on("click", "", function() {
            popupEasy.create(
                window.text.index_5,
                window.textSetting.label_1,
                function() {
                    ajax.send(
                        true,
                        window.url.cpSettingLanguageManage,
                        "post",
                        {
                            'event': "deleteLanguage",
                            'code': code,
                            'token': window.session.token
                        },
                        "json",
                        false,
                        true,
                        "application/x-www-form-urlencoded; charset=UTF-8",
                        null,
                        function(xhr) {
                            ajax.reply(xhr, "");
                            
                            if (xhr.response.messages.success !== undefined) {
                                $("#setting_language_manage_close").click();
                                
                                $("#form_setting_language").find("option").eq(index).remove();
                                $("#language_text_container").find(".mdc-list-item").eq(index - 1).remove();
                            }
                        },
                        null,
                        null
                    );
                }
            );
        });
        
        $("#setting_language_manage_close").on("click", "", function() {
            $("#form_setting_languageManageCode").val("");
            $("#form_setting_languageManageDate").val("");
            
            $("#setting_language_manage_container").hide();
        });
    }
}