/* global utility, ajax, language, popupEasy */

var controlPanelPage = new ControlPanelPage();

function ControlPanelPage() {
    // Vars
    var self = this;
    
    var widthType = "";
    var widthTypeOld = "";
    
    var profileFocus = false;
    
    // Properties
    self.setProfileFocus = function(value) {
        profileFocus = value;
    };
    
    // ---
    
    self.getProfileFocus = function() {
        return profileFocus;
    };
    
    // Functions public
    $(window).resize(function() {
        resetView();
    });
    
    self.init = function() {
        resetView();
        
        selection();
        
        fieldsVisibility("#form_cp_page_creation");
        
        $("#form_page_argument").jqte();
        
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
    
    // Function private
    function selection() {
        var table = new Table();
        table.setButtonsStatus("show");
        table.init(window.url.cpPagesSelection, "#cp_pages_selection_desktop_result", true);
        table.search(true);
        table.pagination(true);
        table.sort(true);
        
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
                    
                    table.populate(xhr);
                },
                null,
                null
            );
        });
        
        $(document).on("click", "#cp_pages_selection_desktop_result .delete_all", function() {
            popupEasy.create(
                window.text.warning,
                window.textPage.deleteAllPages,
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
        
        $(document).on("click", "#cp_pages_selection_desktop_result .cp_page_deletion", function() {
            var id = $.trim($(this).parents("tr").find(".id_column").text());
            
            deletion(id);
        });
        
        $("#cp_pages_selection_send").on("click", "", function(event) {
            var id = $.trim($("#cp_pages_selection_desktop").find(".checkbox_column input:checked").parents("tr").find(".id_column").text());

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

        $("#form_cp_pages_selection").on("submit", "", function(event) {
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
            $("#cp_page_selection_result").html(xhr.response.render);

            profile();
            
            $("#cp_page_deletion").on("click", "", function() {
               deletion(xhr.urlExtra);
            });
        }
    }
    
    function profile() {
        fieldsVisibility("#form_cp_page_profile");
        
        $("#form_page_argument").jqte();
        
        utility.wordTag("#form_page_roleId");
        
        language.page();
        
        $("#form_cp_page_profile").find(".form-control").focus(function() {
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
            window.textPage.deletePage,
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
                                xhr.response.values.text + xhr.response.values.button + xhr.response.values.select,
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
    
    function resetView() {
        widthType = utility.widthCheck(992);
        
        if ((widthType === "desktop" || widthType === "mobile") && widthTypeOld !== widthType) {
            $("#cp_pages_selection_desktop").find(".checkbox_column input[type='checkbox']").prop("checked", false);
            $("#cp_pages_selection_mobile").find("select").val("");
            $("#cp_page_selection_result").html("");
            
            profileFocus = false;
            
            widthTypeOld = widthType;
        }
    }
}