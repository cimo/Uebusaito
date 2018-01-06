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
        selectionDesktop();
        
        selectionMobile();
        
        fieldsVisibility("#form_cp_page_creation");
        
        positionInMenu(true);
        
        utility.wordTag("#form_page_roleUserId");
        
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

        if (utility.checkWidthType() === "desktop") {
            if (selectionSended === true) {
                selectionId = $("#form_cp_page_selection_mobile").find("select option:selected").val();

                selectionSended = false;
            }

            if (selectionId >= 0) {
                $("#cp_page_selection_result_desktop").find(".checkbox_column input[type='checkbox']").prop("checked", false);

                var idColumns = $("#cp_page_selection_result_desktop").find(".checkbox_column input[type='checkbox']").parents("tr").find(".id_column");

                $.each(idColumns, function(key, value) {
                    if ($(value).text().trim() === String(selectionId))
                        $(value).parents("tr").find(".checkbox_column input").prop("checked", true);
                });
            }
        }
        else {
            if (selectionSended === true) {
                selectionId = $("#cp_page_selection_result_desktop").find(".checkbox_column input[type='checkbox']:checked").parents("tr").find(".id_column").text().trim();

                selectionSended = false;
            }

            if (selectionId > 0)
                $("#form_cp_page_selection_mobile").find("select option[value='" + selectionId + "']").prop("selected", true);
        }
    };
    
    // Function private
    function selectionDesktop() {
        var tableAndPagination = new TableAndPagination();
        tableAndPagination.setButtonsStatus("show");
        tableAndPagination.init(window.url.cpPageSelection, "#cp_page_selection_result_desktop", true);
        tableAndPagination.search(true);
        tableAndPagination.pagination(true);
        tableAndPagination.sort(true);
        
        $(document).on("click", "#cp_page_selection_result_desktop .refresh", function() {
            ajax.send(
                true,
                false,
                window.url.cpPageSelection,
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
        
        $(document).on("click", "#cp_page_selection_result_desktop .delete_all", function() {
            popupEasy.create(
                window.text.warning,
                "<p>" + window.textPage.label_2 + "</p>",
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

                            $.each($("#cp_page_selection_result_desktop").find("table .id_column"), function(key, value) {
                                $(value).parents("tr").remove();
                            });
                            
                            $("#cp_page_selection_result").html("");
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
        
        $(document).on("click", "#cp_page_selection_result_desktop .cp_page_deletion", function() {
            var id = $.trim($(this).parents("tr").find(".id_column").text().trim());
            
            deletion(id);
        });
        
        $("#cp_page_selection_button_desktop").on("click", "", function(event) {
            var id = $.trim($(this).parent().find(".checkbox_column input:checked").parents("tr").find(".id_column").text().trim());

            ajax.send(
                true,
                true,
                window.url.cpPageProfileResult,
                "post",
                {
                    'event': "result",
                    'id': id,
                    'token': window.session.token
                },
                "json",
                false,
                function() {
                    $("#cp_page_selection_result").html("");
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
        $("#form_cp_page_selection_mobile").on("submit", "", function(event) {
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
            
            $("#cp_page_selection_result").html(xhr.response.render);

            fieldsVisibility("#form_cp_page_profile");
        
            positionInMenu(false);

            language.page();

            utility.selectWithDisabledElement("#form_page_parent", xhr);

            utility.wordTag("#form_page_roleUserId");

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

                        if ($.isEmptyObject(xhr.response.messages.success) === false) {
                            profileFocus = false;

                            $("#cp_page_selection_result").html("");
                        }

                    },
                    null,
                    null
                );
            });
            
            $("#cp_page_deletion").on("click", "", function() {
               deletion(null);
            });
        }
    }
    
    function positionInMenu(isCreation) {
        if (isCreation === false)
            $("#form_page_positionInMenu").find("option")[0].remove();
        
        utility.selectSortable("#form_page_positionInMenu", null, "#form_page_sort", isCreation);
        
        $("#page_menu_sort").find("i").on("click", "", function() {
            utility.selectSortable("#form_page_positionInMenu", $(this), "#form_page_sort", isCreation);
        });
        
        $("#form_page_parent").on("change", "", function() {
            ajax.send(
                true,
                false,
                window.url.cpPageProfileSort,
                "post",
                {
                    'event': "refresh",
                    'id': $(this).val(),
                    'token': window.session.token
                },
                "json",
                false,
                null,
                function(xhr) {
                    ajax.reply(xhr, "");
                    
                    var optionSelected = $("#form_page_positionInMenu").find("option:selected");
                    
                    $("#form_page_positionInMenu").find("option").remove();
                    
                    $.each(xhr.response.values.pageRows, function(key, value) {
                        $("#form_page_positionInMenu").append($("<option></option>").attr("value", value).text(key));
                    });
                    
                    $("#form_page_positionInMenu").append($("<option selected=\"selected\"></option>").attr("value", optionSelected.val()).text(optionSelected.text()));
                    
                    utility.selectSortable("#form_page_positionInMenu", null, "#form_page_sort", isCreation);
                },
                null,
                null
            );
        });
    }
    
    function deletion(id) {
        popupEasy.create(
            window.text.warning,
            "<p>" + window.textPage.label_1 + "</p>",
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
                    null,
                    function(xhr) {
                        if (xhr.response.values.text !== undefined && xhr.response.values.button !== undefined && xhr.response.values.pageHtml !== undefined) {
                            popupEasy.create(
                                window.text.warning,
                                "<div>" + xhr.response.values.text + xhr.response.values.button + xhr.response.values.pageHtml + "</div>",
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
                                    null,
                                    function(xhr) {
                                        popupEasy.close();

                                        ajax.reply(xhr, "");
                                        
                                        deleteResponse(xhr);
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
                                    null,
                                    function(xhr) {
                                        popupEasy.close();

                                        ajax.reply(xhr, "");
                                        
                                        deleteResponse(xhr);
                                    },
                                    null,
                                    null
                                );
                            });

                            utility.selectWithDisabledElement("#cp_page_deletion_parent_new", xhr);
                        }
                        else {
                            ajax.reply(xhr, "");
                            
                            deleteResponse(xhr);
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
    
    function deleteResponse(xhr) {
        if (xhr.response.messages.success !== undefined) {
            $.each($("#cp_page_selection_result_desktop").find("table .id_column"), function(key, value) {
                if (xhr.response.values.id !== undefined && xhr.response.values.id === $.trim($(value).text()) ||
                        xhr.response.values.removedId !== undefined && jQuery.inArray($.trim($(value).text()), xhr.response.values.removedId) !== -1)
                    $(value).parents("tr").remove();
            });

            $("#form_page_selection_id").find("option[value='" + xhr.response.values.id + "']").remove();

            $("#cp_page_selection_result").html("");
        }
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
            $(id).find("#form_page_menuName").parents(".form-group").hide();
            $(id).find("#form_page_positionInMenu").parents(".form-group").hide();
        }
        else {
            $(id).find("#form_page_menuName").parents(".form-group").show();
            $(id).find("#form_page_positionInMenu").parents(".form-group").show();
        }
    }
    
    function fieldsVisibilityLink(id) {
        if ($(id).find("#form_page_onlyLink").val() === "0")
            $(id).find("#form_page_link").parents(".form-group").hide();
        else
            $(id).find("#form_page_link").parents(".form-group").show();
    }
}