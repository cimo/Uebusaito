/* global utility, ajax, popupEasy */

var controlPanelRole = new ControlPanelRole();

function ControlPanelRole() {
    // Vars
    var self = this;
    
    var selectionSended = false;
    var selectionId = -1;
    
    // Properties
    
    // Functions public
    self.init = function() {
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
                    ajax.reply(xhr, "#" + event.currentTarget.id);
                },
                null,
                null
            );
        });
    };
    
    self.changeView = function() {
        if (utility.getWidthType() === "desktop") {
            if (selectionSended === true) {
                selectionId = $("#cp_roles_selection_mobile").find("select option:selected").val();

                selectionSended = false;
            }

            if (selectionId >= 0) {
                $("#cp_roles_selection_desktop_result").find(".checkbox_column input[type='checkbox']").prop("checked", false);

                var idColumns = $("#cp_roles_selection_desktop_result").find(".checkbox_column input[type='checkbox']").parents("tr").find(".id_column");

                $.each(idColumns, function(key, value) {
                    if ($(value).text().trim() === String(selectionId))
                        $(value).parents("tr").find(".checkbox_column input").prop("checked", true);
                });
            }
        }
        else {
            if (selectionSended === true) {
                selectionId = $("#cp_roles_selection_desktop_result").find(".checkbox_column input[type='checkbox']:checked").parents("tr").find(".id_column").text().trim();

                selectionSended = false;
            }

            if (selectionId > 0)
                $("#cp_roles_selection_mobile").find("select option[value='" + selectionId + "']").prop("selected", true);
        }
    };
    
    // Function private
    function selection() {
        var tableAndPagination = new TableAndPagination();
        tableAndPagination.setButtonsStatus("show");
        tableAndPagination.init(window.url.cpRolesSelection, "#cp_roles_selection_desktop_result", true);
        tableAndPagination.search(true);
        tableAndPagination.pagination(true);
        tableAndPagination.sort(true);
        
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
                    ajax.reply(xhr, "");
                    
                    tableAndPagination.populate(xhr);
                },
                null,
                null
            );
        });
        
        $(document).on("click", "#cp_roles_selection_desktop_result .delete_all", function() {
            popupEasy.create(
                window.text.warning,
                "<p>" + window.textRole.label_2 + "</p>",
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
                            ajax.reply(xhr, "");

                            tableAndPagination.populate(xhr);
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
            selectionSended = true;
            
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
            "<p>" + window.textRole.label_1 + "</p>",
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
}