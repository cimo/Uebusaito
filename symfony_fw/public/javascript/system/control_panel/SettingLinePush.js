/* global ajax, popupEasy, materialDesign */

var controlPanelSettingLinePush = new ControlPanelSettingLinePush();

function ControlPanelSettingLinePush() {
    // Vars
    var self = this;
    
    // Properties
    
    // Functions public
    self.init = function() {
        $("#form_cp_setting_line_push_create").on("submit", "", function(event) {
            event.preventDefault();
            
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
                    
                    var name = $("#form_settingLinePush_name").val();
                    
                    if (xhr.response.values.wordTagListHtml !== undefined) {
                        resetField();

                        materialDesign.refresh();
                        
                        $("#form_cp_setting_line_push_create").find(".wordTag_container").html(xhr.response.values.wordTagListHtml);
                    }
                },
                null,
                null
            );
        });
        
        $("#form_cp_setting_line_push_create").on("click", ".button_reset", function(event) {
            ajax.send(
                true,
                window.url.cpSettingLinePushReset,
                "post",
                {
                    'event': "reset",
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
                        resetField();

                        materialDesign.refresh();
                    }
                },
                null,
                null
            );
        });
        
        $("#form_cp_setting_line_push_create .wordTag_container").on("click", ".edit", function(event) {
            if ($(event.target).hasClass("delete") === true)
                return;
            
            var id = $.trim($(this).parent().find(".mdc-chip__text").attr("data-id"));
            
            ajax.send(
                true,
                window.url.cpSettingLinePushCreate,
                "post",
                {
                    'event': "profile",
                    'id': id,
                    'token': window.session.token
                },
                "json",
                false,
                true,
                "application/x-www-form-urlencoded; charset=UTF-8",
                null,
                function(xhr) {
                    ajax.reply(xhr, "");
                    
                    if (xhr.response.values.wordTagListHtml !== undefined) {
                        $("#form_settingLinePush_name").val(xhr.response.values.entity[0]);
                        $("#form_settingLinePush_userId").val(xhr.response.values.entity[1]);
                        $("#form_settingLinePush_accessToken").val(xhr.response.values.entity[2]);
                        
                        materialDesign.refresh();
                        
                        $("#form_cp_setting_line_push_create").find(".wordTag_container").html(xhr.response.values.wordTagListHtml);
                    }
                },
                null,
                null
            );
        });
        
        $("#form_cp_setting_line_push_create .wordTag_container").on("click", ".delete", function(event) {
            var id = $.trim($(this).parent().find(".mdc-chip__text").attr("data-id"));
            
            popupEasy.create(
                window.text.index_5,
                window.textSettingLinePush.label_1,
                function() {
                    ajax.send(
                        true,
                        window.url.cpSettingLinePushDelete,
                        "post",
                        {
                            'event': "delete",
                            'id': id,
                            'token': window.session.token
                        },
                        "json",
                        false,
                        true,
                        "application/x-www-form-urlencoded; charset=UTF-8",
                        null,
                        function(xhr) {
                            ajax.reply(xhr, "");

                            if (xhr.response.values.wordTagListHtml !== undefined) {
                                resetField();

                                $("#form_cp_setting_line_push_create").find(".wordTag_container").html(xhr.response.values.wordTagListHtml);
                            }
                        },
                        null,
                        null
                    );
                }
            );
        });
    };
    
    // Function private
    function resetField() {
        $("#form_settingLinePush_name").val("");
        $("#form_settingLinePush_name").parent().find("label").removeClass("mdc-floating-label--float-above");

        $("#form_settingLinePush_userId").val("");
        $("#form_settingLinePush_userId").parent().find("label").removeClass("mdc-floating-label--float-above");

        $("#form_settingLinePush_accessToken").val("");
        $("#form_settingLinePush_accessToken").parent().find("label").removeClass("mdc-floating-label--float-above");
    }
}