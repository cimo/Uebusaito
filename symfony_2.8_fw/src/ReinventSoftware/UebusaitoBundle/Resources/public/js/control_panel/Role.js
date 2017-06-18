/* global utility, ajax, popupEasy */

var controlPanelRole = new ControlPanelRole();

function ControlPanelRole() {
    // Vars
    var self = this;
    
    var widthType = "";
    var widthTypeOld = "";
    
    // Properties
    
    // Functions public
    $(window).resize(function() {
        resetView();
    });
    
    self.init = function() {
        resetView();
        
        selection();
        
        $("#form_role_level").on("keyup", "", function() {
            $(this).val($(this).val().toUpperCase());
        });
        
        $("#form_cp_role_creation").on("submit", "", function(event) {
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
                    if (xhr.response.session !== undefined && xhr.response.session.userActivity !== "") {
                        ajax.reply(xhr, "");

                        return;
                    }
                    
                    ajax.reply(xhr, "#" + event.currentTarget.id);
                },
                null,
                null
            );
        });
    };
    
    // Function private
    function selection() {
        var table = new Table();
        table.setButtonsStatus("show");
        table.init(window.url.cpRolesSelection, "#cp_roles_selection_desktop_result", true);
        table.search(true);
        table.pagination(true);
        table.sort(true);
        
        $(document).on("click", "#cp_roles_selection_desktop_result .refresh", function() {
            ajax.send(
                true,
                false,
                window.url.cpRolesSelection,
                "post",
                {
                    'event': "refresh",
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
                    
                    table.populate(xhr);
                },
                null,
                null
            );
        });
        
        $(document).on("click", "#cp_roles_selection_desktop_result .delete_all", function() {
            popupEasy.create(
                window.text.warning,
                window.textRole.deleteAllRoles,
                function() {
                    popupEasy.close();
                    
                    ajax.send(
                        true,
                        false,
                        window.url.cpRoleDeletion,
                        "post",
                        {
                            'event': "deleteAll",
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

                            table.populate(xhr);
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
        
        $(document).on("click", "#cp_roles_selection_desktop_result .cp_role_deletion", function() {
            var id = $.trim($(this).parents("tr").find(".id_column").text());
            
            deletion(id);
        });
        
        $("#cp_roles_selection_send").on("click", "", function(event) {
            var id = $.trim($("#cp_roles_selection_desktop_result").find(".checkbox_column input:checked").parents("tr").find(".id_column").text());

            ajax.send(
                true,
                true,
                window.url.cpRolesSelection,
                "post",
                {
                    'id': id,
                    'token': window.session.token
                },
                "json",
                false,
                function() {
                    $("#cp_role_selection_result").html("");
                },
                function(xhr) {
                    if (xhr.response.session !== undefined && xhr.response.session.userActivity !== "") {
                        ajax.reply(xhr, "");

                        return;
                    }
                    
                    selectionResult(xhr, "#" + event.currentTarget.id);
                },
                null,
                null
            );
        });

        $("#form_cp_roles_selection").on("submit", "", function(event) {
            event.preventDefault();

            ajax.send(
                true,
                true,
                $(this).prop("action"),
                $(this).prop("method"),
                $(this).serialize(),
                "json",
                false,
                function() {
                    $("#cp_role_selection_result").html("");
                },
                function(xhr) {
                    if (xhr.response.session !== undefined && xhr.response.session.userActivity !== "") {
                        ajax.reply(xhr, "");

                        return;
                    }
                    
                    selectionResult(xhr, "#" + event.currentTarget.id);
                },
                null,
                null
            );
        });
    }
    
    function selectionResult(xhr, tag) {
        ajax.reply(xhr, tag);
        
        if ($.isEmptyObject(xhr.response) === false && xhr.response.render !== undefined) {
            $("#cp_role_selection_result").html(xhr.response.render);

            profile();

            $("#cp_role_deletion").on("click", "", function() {
               deletion(xhr.urlExtra);
            });
        }
    }
    
    function profile() {
        $("#form_role_level").on("keyup", "", function() {
            $(this).val($(this).val().toUpperCase());
        });
        
        $("#form_cp_role_profile").on("submit", "", function(event) {
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
                    if (xhr.response.session !== undefined && xhr.response.session.userActivity !== "") {
                        ajax.reply(xhr, "");

                        return;
                    }
                    
                    ajax.reply(xhr, "#" + event.currentTarget.id);
                },
                null,
                null
            );
        });
    }
    
    function deletion(id) {
        popupEasy.create(
            window.text.warning,
            window.textRole.deleteRole,
            function() {
                popupEasy.close();

                ajax.send(
                    true,
                    false,
                    window.url.cpRoleDeletion,
                    "post",
                    {
                        'event': "delete",
                        'id': id,
                        'token': window.session.token
                    },
                    "json",
                    false,
                    function() {
                        $("#cp_role_selection_result").html("");
                    },
                    function(xhr) {
                        if (xhr.response.session !== undefined && xhr.response.session.userActivity !== "") {
                            ajax.reply(xhr, "");

                            return;
                        }

                        ajax.reply(xhr, "");
                    },
                    null,
                    null
                );
            },
            function() {
                popupEasy.close();
            }
        );
    }
    
    function resetView() {
        widthType = utility.widthCheck(992);
        
        if ((widthType === "desktop" || widthType === "mobile") && widthTypeOld !== widthType) {
            $("#cp_roles_selection_desktop_result").find(".checkbox_column input[type='checkbox']").prop("checked", false);
            $("#cp_roles_selection_mobile").find("select").val("");
            $("#cp_role_selection_result").html("");
            
            widthTypeOld = widthType;
        }
    }
}