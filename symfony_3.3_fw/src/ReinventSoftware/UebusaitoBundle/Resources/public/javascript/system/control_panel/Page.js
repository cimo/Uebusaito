/* global utility, ajax, language, popupEasy, wysiwyg */

var controlPanelPage = new ControlPanelPage();

function ControlPanelPage() {
    // Vars
    var self = this;
    
    var selectionSended = false;
    var selectionId = -1;
    
    var profileFocus = false;
    
    // Properties
    self.getProfileFocus = function() {
        return profileFocus;
    };
    
    // ---
    
    self.setProfileFocus = function(value) {
        profileFocus = value;
    };
    
    // Functions public
    self.init = function() {
        selection();
        
        fieldsVisibility("#form_cp_page_creation");
        
        utility.wordTag("#form_page_roleId");
        
        $("#form_cp_page_creation").on("submit", "", function(event) {
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
        profileFocus = false;

        if (utility.getWidthType() === "desktop") {
            if (selectionSended === true) {
                selectionId = $("#cp_pages_selection_mobile").find("select option:selected").val();

                selectionSended = false;
            }

            if (selectionId >= 0) {
                $("#cp_pages_selection_desktop_result").find(".checkbox_column input[type='checkbox']").prop("checked", false);

                var idColumns = $("#cp_pages_selection_desktop_result").find(".checkbox_column input[type='checkbox']").parents("tr").find(".id_column");

                $.each(idColumns, function(key, value) {
                    if ($(value).text().trim() === String(selectionId))
                        $(value).parents("tr").find(".checkbox_column input").prop("checked", true);
                });
            }
        }
        else {
            if (selectionSended === true) {
                selectionId = $("#cp_pages_selection_desktop_result").find(".checkbox_column input[type='checkbox']:checked").parents("tr").find(".id_column").text().trim();

                selectionSended = false;
            }

            if (selectionId > 0)
                $("#cp_pages_selection_mobile").find("select option[value='" + selectionId + "']").prop("selected", true);
        }
    };
    
    // Function private
    function selection() {
        var tableAndPagination = new TableAndPagination();
        tableAndPagination.setButtonsStatus("show");
        tableAndPagination.init(window.url.cpPagesSelection, "#cp_pages_selection_desktop_result", true);
        tableAndPagination.search(true);
        tableAndPagination.pagination(true);
        tableAndPagination.sort(true);
        
        $(document).on("click", "#cp_pages_selection_desktop_result .refresh", function() {
            ajax.send(
                true,
                false,
                window.url.cpPagesSelection,
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
        
        $(document).on("click", "#cp_pages_selection_desktop_result .delete_all", function() {
            popupEasy.create(
                window.text.warning,
                "<p>" + window.textPage.deleteAllPages + "</p>",
                function() {
                    popupEasy.close();
                    
                    ajax.send(
                        true,
                        false,
                        window.url.cpPageDeletion,
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
        
        $(document).on("click", "#cp_pages_selection_desktop_result .cp_page_deletion", function() {
            var id = $.trim($(this).parents("tr").find(".id_column").text().trim());
            
            deletion(id);
        });
        
        $("#cp_pages_button_selection_desktop").on("click", "", function(event) {
            var id = $.trim($("#cp_pages_selection_desktop").find(".checkbox_column input:checked").parents("tr").find(".id_column").text().trim());

            ajax.send(
                true,
                true,
                window.url.cpPagesSelection,
                "post",
                {
                    'id': id,
                    'token': window.session.token
                },
                "json",
                false,
                function() {
                    $("#cp_page_selection_result").html("");
                },
                function(xhr) {
                    selectionResult(xhr, "#" + event.currentTarget.id);
                },
                null,
                null
            );
        });

        $("#cp_pages_form_selection_mobile").on("submit", "", function(event) {
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
                    $("#cp_page_selection_result").html("");
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
            
            $("#cp_page_selection_result").html(xhr.response.render);

            profile(xhr);
            
            $("#cp_page_deletion").on("click", "", function() {
               deletion(xhr.urlExtra);
            });
        }
    }
    
    function profile(xhr) {
        fieldsVisibility("#form_cp_page_profile");
        
        utility.wordTag("#form_page_roleId");
        
        language.page();
        
        utility.selectWithDisabledElement("#form_page_parent", xhr);
        
        wysiwyg.init("#form_page_argument", $("#form_cp_page_profile").find("input[type='submit']"));
        
        $("#form_cp_page_profile").find(".form-control").focus(function() {
            profileFocus = true;
        });
        
        $("#form_cp_page_profile").find("#wysiwyg .editor").focus(function() {
            profileFocus = true;
        });
        
        $("#form_cp_page_profile").on("submit", "", function(event) {
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
                    
                    if ($.isEmptyObject(xhr.response.messages.success) === false)
                        profileFocus = false;
                },
                null,
                null
            );
        });
    }
    
    function deletion(id) {
        popupEasy.create(
            window.text.warning,
            "<p>" + window.textPage.deletePage + "</p>",
            function() {
                popupEasy.close();

                ajax.send(
                    true,
                    false,
                    window.url.cpPageDeletion,
                    "post",
                    {
                        'event': "delete",
                        'id': id,
                        'token': window.session.token
                    },
                    "json",
                    false,
                    function() {
                        $("#cp_page_selection_result").html("");
                    },
                    function(xhr) {
                        if (xhr.response.messages !== undefined)
                            ajax.reply(xhr, "");
                        else if ($.isEmptyObject(xhr.response.values) === false) {
                            popupEasy.create(
                                window.text.warning,
                                "<p>" + xhr.response.values.text + xhr.response.values.button + xhr.response.values.select + "</p>",
                                null,
                                null
                            );

                            $("#cp_page_deletion_parent_all").on("click", "", function() {
                                popupEasy.close();
                                
                                ajax.send(
                                    true,
                                    true,
                                    window.url.cpPageDeletion,
                                    "post",
                                    {
                                        'event': "parentAll",
                                        'id': id,
                                        'token': window.session.token
                                    },
                                    "json",
                                    false,
                                    function() {
                                        $("#cp_page_selection_result").html("");
                                    },
                                    function(xhr) {
                                        popupEasy.close();

                                        ajax.reply(xhr, "");
                                    },
                                    null,
                                    null
                                );
                            });

                            $("#cp_page_deletion_parent_new").on("change", "", function() {
                                popupEasy.close();
                                
                                ajax.send(
                                    true,
                                    true,
                                    window.url.cpPageDeletion,
                                    "post",
                                    {
                                        'event': "parentNew",
                                        'id': id,
                                        'parentNew': $(this).val(),
                                        'token': window.session.token
                                    },
                                    "json",
                                    false,
                                    function() {
                                        $("#cp_page_selection_result").html("");
                                    },
                                    function(xhr) {
                                        popupEasy.close();

                                        ajax.reply(xhr, "");
                                    },
                                    null,
                                    null
                                );
                            });

                            utility.selectWithDisabledElement("#cp_page_deletion_parent_new", xhr);
                        }
                        else
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
    
    function fieldsVisibility(id) {
        fieldsVisibilityMenu(id);
        
        fieldsVisibilityLink(id);
        
        $("#form_page_showInMenu").on("change", "", function() {
            fieldsVisibilityMenu(id);
        });
        
        $("#form_page_onlyLink").on("change", "", function() {
            fieldsVisibilityLink(id);
        });
    }
    
    function fieldsVisibilityMenu(id) {
        if ($(id).find("#form_page_showInMenu").val() === "0") {
            $(id).find("#form_page_menuName").val("-");
            $(id).find("#form_page_menuName").prev().hide();
            $(id).find("#form_page_menuName").hide();
        }
        else {
            $(id).find("#form_page_menuName").prev().show();
            $(id).find("#form_page_menuName").show();
        }
    }
    
    function fieldsVisibilityLink(id) {
        if ($(id).find("#form_page_onlyLink").val() === "0") {
            $(id).find("#form_page_link").val("-");
            $(id).find("#form_page_link").prev().hide();
            $(id).find("#form_page_link").hide();
        }
        else {
            $(id).find("#form_page_link").prev().show();
            $(id).find("#form_page_link").show();
        }
    }
}