/* global ajax, popupEasy, materialDesign */

var controlPanelSettingSlackIw = new ControlPanelSettingSlackIw();

function ControlPanelSettingSlackIw() {
    // Vars
    var self = this;
    
    // Properties
    
    // Functions public
    self.init = function() {
        $("#form_cp_setting_slack_iw_create").on("submit", "", function(event) {
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
                    
                    var name = $("#form_settingSlackIw_name").val();
                    
                    if (xhr.response.values.settingSlackIwListHtml !== undefined) {
                        resetField();

                        materialDesign.refresh();
                        
                        $("#form_cp_setting_slack_iw_create").find(".wordTag_container").html(xhr.response.values.settingSlackIwListHtml);
                    }
                },
                null,
                null
            );
        });
        
        $("#form_cp_setting_slack_iw_create").on("click", ".button_reset", function(event) {
            ajax.send(
                true,
                window.url.cpSettingSlackIwReset,
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
        
        $("#form_cp_setting_slack_iw_create .wordTag_container").on("click", ".edit", function(event) {
            var id = $.trim($(this).parent().find(".mdc-chip__text").attr("data-id"));
            
            ajax.send(
                true,
                window.url.cpSettingSlackIwCreate,
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
                    
                    if (xhr.response.values.settingSlackIwListHtml !== undefined) {
                        $("#form_settingSlackIw_name").val(xhr.response.values.entity[0]);
                        $("#form_settingSlackIw_hook").val(xhr.response.values.entity[1]);
                        $("#form_settingSlackIw_channel").val(xhr.response.values.entity[2]);
                        
                        materialDesign.refresh();
                        
                        $("#form_cp_setting_slack_iw_create").find(".wordTag_container").html(xhr.response.values.settingSlackIwListHtml);
                    }
                },
                null,
                null
            );
        });
        
        $("#form_cp_setting_slack_iw_create .wordTag_container").on("click", ".delete", function(event) {
            var id = $.trim($(this).parent().find(".mdc-chip__text").attr("data-id"));
            
            popupEasy.create(
                window.text.index_5,
                window.textSettingSlackIw.label_1,
                function() {
                    ajax.send(
                        true,
                        window.url.cpSettingSlackIwDelete,
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

                            if (xhr.response.values.settingSlackIwListHtml !== undefined) {
                                resetField();

                                $("#form_cp_setting_slack_iw_create").find(".wordTag_container").html(xhr.response.values.settingSlackIwListHtml);
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
        $("#form_settingSlackIw_name").val("");
        $("#form_settingSlackIw_name").parent().find("label").removeClass("mdc-floating-label--float-above");

        $("#form_settingSlackIw_hook").val("");
        $("#form_settingSlackIw_hook").parent().find("label").removeClass("mdc-floating-label--float-above");

        $("#form_settingSlackIw_channel").val("");
        $("#form_settingSlackIw_channel").parent().find("label").removeClass("mdc-floating-label--float-above");
    }
}