/* global utility, ajax, popupEasy, materialDesign */

var controlPanelModule = new ControlPanelModule();

function ControlPanelModule() {
    // Vars
    var self = this;
    
    var selectionSended = false;
    var selectionId = -1;
    
    // Properties
    
    // Functions public
    self.init = function() {
        positionDrag();
        
        selectionDesktop();
        
        selectionMobile();
        
        rankInColumn();
        
        $("#form_cp_module_creation").on("submit", "", function(event) {
            event.preventDefault();
            
            ajax.send(
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
        if (utility.checkWidthType() === "mobile") {
            if (selectionSended === true) {
                selectionId = $("#cp_module_selection_mobile").find("select option:selected").val();

                selectionSended = false;
            }

            if (selectionId >= 0) {
                $("#cp_module_selection_result_desktop").find(".checkbox_column input[type='checkbox']").prop("checked", false);

                var idColumns = $("#cp_module_selection_result_desktop").find(".checkbox_column input[type='checkbox']").parents("tr").find(".id_column");

                $.each(idColumns, function(key, value) {
                    if ($(value).text().trim() === String(selectionId))
                        $(value).parents("tr").find(".checkbox_column input").prop("checked", true);
                });
            }
        }
        else {
            if (selectionSended === true) {
                selectionId = $("#cp_module_selection_result_desktop").find(".checkbox_column input[type='checkbox']:checked").parents("tr").find(".id_column").text().trim();

                selectionSended = false;
            }

            if (selectionId >= 0)
                $("#cp_module_selection_mobile").find("select option[value='" + selectionId + "']").prop("selected", true);
        }
        
        rankInColumn();
    };
    
    // Function private
    function positionDrag() {
        $("#cp_module_drag_switch .mdc-switch__native-control").on("click", "", function() {
            if ($(this).is(":checked") === false) {
                utility.sortableModule(false, "#form_module_drag_position");
            
                $("#form_cp_module_drag").submit();
            }
            else
                utility.sortableModule(true, "#form_module_drag_position");
        });
        
        $("#form_cp_module_drag").on("submit", "", function(event) {
            event.preventDefault();
            
            ajax.send(
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
    
    function selectionDesktop() {
        var tableAndPagination = new TableAndPagination();
        tableAndPagination.init();
        tableAndPagination.setButtonsStatus("show");
        tableAndPagination.create(window.url.cpModuleSelection, "#cp_module_selection_result_desktop", true);
        tableAndPagination.search();
        tableAndPagination.pagination();
        tableAndPagination.sort();
        
        $(document).on("click", "#cp_module_selection_result_desktop .refresh", function() {
            ajax.send(
                true,
                window.url.cpModuleSelection,
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
        
        $(document).on("click", "#cp_module_selection_result_desktop .delete_all", function() {
            popupEasy.create(
                window.text.index_5,
                window.textModule.label_2,
                function() {
                    ajax.send(
                        true,
                        window.url.cpModuleDeletion,
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

                            $.each($("#cp_module_selection_result_desktop").find("table .id_column"), function(key, value) {
                                $(value).parents("tr").remove();
                            });
                            
                            $("#cp_module_selection_result").html("");
                            
                            popupEasy.close();
                        },
                        null,
                        null
                    );
                }
            );
        });
        
        $(document).on("click", "#cp_module_selection_result_desktop .cp_module_deletion", function() {
            var id = $.trim($(this).parents("tr").find(".id_column").text());
            
            deletion(id);
        });
        
        $(document).on("click", "#cp_module_selection_button_desktop", function(event) {
            var id = $.trim($(this).parent().find(".checkbox_column input:checked").parents("tr").find(".id_column").text());

            ajax.send(
                true,
                window.url.cpModuleProfileResult,
                "post",
                {
                    'event': "result",
                    'id': id,
                    'token': window.session.token
                },
                "json",
                false,
                function() {
                    $("#cp_module_selection_result").html("");
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
        $(document).on("submit", "#form_cp_module_selection_mobile", function(event) {
            event.preventDefault();

            ajax.send(
                true,
                $(this).prop("action"),
                $(this).prop("method"),
                $(this).serialize(),
                "json",
                false,
                function() {
                    $("#cp_module_selection_result").html("");
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
            
            $("#cp_module_selection_result").html(xhr.response.render);
            
            rankInColumn();
            
            materialDesign.refresh();
            materialDesign.fix();

            $("#form_cp_module_profile").on("submit", "", function(event) {
                event.preventDefault();

                ajax.send(
                    true,
                    $(this).prop("action"),
                    $(this).prop("method"),
                    $(this).serialize(),
                    "json",
                    false,
                    null,
                    function(xhr) {
                        ajax.reply(xhr, "#" + event.currentTarget.id);
                        
                        if (xhr.response.messages.success !== undefined) {
                            $("#cp_module_selection_result").html("");
                            
                            $("#cp_module_selection_result_desktop .refresh").click();
                        }
                    },
                    null,
                    null
                );
            });
            
            $("#cp_module_deletion").on("click", "", function() {
               deletion(null);
            });
        }
    }
    
    function rankInColumn() {
        utility.sortableElement("#module_rankColumnSort", "#form_module_rankColumnSort");
        
        $("#form_module_position").off("change").on("change", "", function() {
            ajax.send(
                true,
                window.url.cpModuleProfileSort,
                "post",
                {
                    'event': "refresh",
                    'position': $(this).val(),
                    'token': window.session.token
                },
                "json",
                false,
                null,
                function(xhr) {
                    ajax.reply(xhr, "");
                    
                    if (xhr.response.values.moduleSortListHtml !== undefined) {
                        $("#module_rankColumnSort").find(".sort_result").html(xhr.response.values.moduleSortListHtml);

                        utility.sortableElement("#module_rankColumnSort", "#form_module_rankColumnSort");
                    }
                },
                null,
                null
            );
        });
    }
    
    function deletion(id) {
        popupEasy.create(
            window.text.index_5,
            window.textModule.label_1,
            function() {
                ajax.send(
                    true,
                    window.url.cpModuleDeletion,
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
                            $.each($("#cp_module_selection_result_desktop").find("table .id_column"), function(key, value) {
                                if (xhr.response.values.id === $.trim($(value).text()))
                                    $(value).parents("tr").remove();
                            });

                            $("#cp_module_selection_result").html("");
                            
                            $("#cp_module_selection_result_desktop .refresh").click();
                            
                            popupEasy.close();
                        }
                    },
                    null,
                    null
                );
            }
        );
    }
}