/* global utility, ajax, popupEasy */

var controlPanelUser = new ControlPanelUser();

function ControlPanelUser() {
    // Vars
    var self = this;
    
    var selectionSended = false;
    var selectionId = -1;
    
    // Properties
    
    // Functions public
    self.init = function() {
        selectionDesktop();
        
        selectionMobile();
        
        utility.wordTag("#form_user_roleId");
        
        $("#form_cp_user_creation").on("submit", "", function(event) {
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
        if (utility.checkWidth() === "desktop") {
            if (selectionSended === true) {
                selectionId = $("#cp_users_selection_mobile").find("select option:selected").val();

                selectionSended = false;
            }

            if (selectionId >= 0) {
                $("#cp_users_selection_desktop_result").find(".checkbox_column input[type='checkbox']").prop("checked", false);

                var idColumns = $("#cp_users_selection_desktop_result").find(".checkbox_column input[type='checkbox']").parents("tr").find(".id_column");

                $.each(idColumns, function(key, value) {
                    if ($(value).text().trim() === String(selectionId))
                        $(value).parents("tr").find(".checkbox_column input").prop("checked", true);
                });
            }
        }
        else {
            if (selectionSended === true) {
                selectionId = $("#cp_users_selection_desktop_result").find(".checkbox_column input[type='checkbox']:checked").parents("tr").find(".id_column").text().trim();

                selectionSended = false;
            }

            if (selectionId > 0)
                $("#cp_users_selection_mobile").find("select option[value='" + selectionId + "']").prop("selected", true);
        }
    };
    
    // Function private
    function selectionDesktop() {
        var tableAndPagination = new TableAndPagination();
        tableAndPagination.setButtonsStatus("show");
        tableAndPagination.init(window.url.cpUsersSelection, "#cp_users_selection_desktop_result", true);
        tableAndPagination.search(true);
        tableAndPagination.pagination(true);
        tableAndPagination.sort(true);
        
        $(document).on("click", "#cp_users_selection_desktop_result .refresh", function() {
            ajax.send(
                true,
                false,
                window.url.cpUsersSelection,
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
        
        $(document).on("click", "#cp_users_selection_desktop_result .delete_all", function() {
            popupEasy.create(
                window.text.warning,
                "<p>" + window.textUser.label_2 + "</p>",
                function() {
                    popupEasy.close();
                    
                    ajax.send(
                        true,
                        false,
                        window.url.cpUserDeletion,
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

                            $.each($("#cp_users_selection_desktop_result").find("table .id_column"), function(key, value) {
                                $(value).parents("tr").remove();
                            });
                            
                            $("#cp_user_selection_result").html("");
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
        
        $(document).on("click", "#cp_users_selection_desktop_result .cp_user_deletion", function() {
            var id = $.trim($(this).parents("tr").find(".id_column").text());
            
            deletion(id);
        });
        
        $("#cp_users_button_selection_desktop").on("click", "", function(event) {
            var id = $.trim($("#cp_users_selection_desktop_result").find(".checkbox_column input:checked").parents("tr").find(".id_column").text());

            ajax.send(
                true,
                true,
                window.url.cpUserProfileResult,
                "post",
                {
                    'event': "result",
                    'id': id,
                    'token': window.session.token
                },
                "json",
                false,
                function() {
                    $("#cp_user_selection_result").html("");
                },
                function(xhr) {
                    profile(xhr, "#" + event.currentTarget.id);
                },
                null,
                null
            );
        });
    }
    
    function selectionMobile() {
        $("#cp_users_form_selection_mobile").on("submit", "", function(event) {
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
                    $("#cp_user_selection_result").html("");
                },
                function(xhr) {
                    profile(xhr, "#" + event.currentTarget.id);
                },
                null,
                null
            );
        });
    }
    
    function profile(xhr, tag) {
        ajax.reply(xhr, tag);
        
        if ($.isEmptyObject(xhr.response) === false && xhr.response.render !== undefined) {
            selectionSended = true;
            
            $("#cp_user_selection_result").html(xhr.response.render);

            utility.wordTag("#form_user_roleId");

            $("#form_cp_user_profile").on("submit", "", function(event) {
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
                        
                        if (xhr.response.messages.success !== undefined)
                            $("#cp_user_selection_result").html("");
                    },
                    null,
                    null
                );
            });
            
            $("#cp_user_deletion").on("click", "", function() {
               deletion(null);
            });
        }
    }
    
    function deletion(id) {
        popupEasy.create(
            window.text.warning,
            "<p>" + window.textUser.label_1 + "</p>",
            function() {
                popupEasy.close();

                ajax.send(
                    true,
                    false,
                    window.url.cpUserDeletion,
                    "post",
                    {
                        'event': "delete",
                        'id': id,
                        'token': window.session.token
                    },
                    "json",
                    false,
                    null,
                    function(xhr) {
                        ajax.reply(xhr, "");
                        
                        if (xhr.response.messages.success !== undefined) {
                            $.each($("#cp_users_selection_desktop_result").find("table .id_column"), function(key, value) {
                                if (xhr.response.values.id === $.trim($(value).text()))
                                    $(value).parents("tr").remove();
                            });

                            $("#form_users_selection_id").find("option[value='" + xhr.response.values.id + "']").remove();

                            $("#cp_user_selection_result").html("");
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
    }
}