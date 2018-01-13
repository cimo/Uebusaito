/* global utility, ajax, popupEasy */

var controlPanelModule = new ControlPanelModule();

function ControlPanelModule() {
    // Vars
    var self = this;
    
    var selectionSended = false;
    var selectionId = -1;
    
    // Properties
    
    // Functions public
    self.init = function() {
        sortableDrag();
        
        selectionDesktop();
        
        selectionMobile();
        
        positionInColumn(true);
        
        $("#form_cp_module_creation").on("submit", "", function(event) {
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
        $("#module_drag_switch").bootstrapSwitch("state", false, true);
        utility.sortableDragModules(false, "#form_module_drag_sort");

        if (utility.checkWidthType() === "desktop") {
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

            if (selectionId > 0)
                $("#cp_module_selection_mobile").find("select option[value='" + selectionId + "']").prop("selected", true);
        }
    };
    
    // Function private
    function sortableDrag() {
        $("#module_drag_switch").bootstrapSwitch("state", false);
        
        $("#module_drag_switch").on("switchChange.bootstrapSwitch", "", function(event, state) {
            utility.sortableDragModules(state, "#form_module_drag_sort");
            
            if (state === false)
                $("#form_cp_module_drag").submit();
        });
        
        $("#form_cp_module_drag").on("submit", "", function(event) {
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
    
    function selectionDesktop() {
        var tableAndPagination = new TableAndPagination();
        tableAndPagination.setButtonsStatus("show");
        tableAndPagination.init(window.url.cpModuleSelection, "#cp_module_selection_result_desktop", true);
        tableAndPagination.search(true);
        tableAndPagination.pagination(true);
        tableAndPagination.sort(true);
        
        $(document).on("click", "#cp_module_selection_result_desktop .refresh", function() {
            ajax.send(
                true,
                false,
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
                window.text.warning,
                "<p>" + window.textModule.label_2 + "</p>",
                function() {
                    popupEasy.close();
                    
                    ajax.send(
                        true,
                        false,
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
        
        $(document).on("click", "#cp_module_selection_result_desktop .cp_module_deletion", function() {
            var id = $.trim($(this).parents("tr").find(".id_column").text());
            
            deletion(id);
        });
        
        $(document).on("click", "#cp_module_selection_button_desktop", function(event) {
            var id = $.trim($(this).parent().find(".checkbox_column input:checked").parents("tr").find(".id_column").text());

            ajax.send(
                true,
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

            positionInColumn(false);

            $("#form_cp_module_profile").on("submit", "", function(event) {
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
                            $("#cp_module_selection_result").html("");
                    },
                    null,
                    null
                );
            });

            var selected = $("#form_module_positionInColumn").find(":selected").val();

            if ($("#panel_id_" + selected).parent().hasClass("settings_hide") === true) {
                $("#form_module_position").parents(".form-group").hide();
                $("#form_module_positionInColumn").parents(".form-group").hide();
            }

            $("#cp_module_deletion").on("click", "", function() {
               deletion(null);
            });
        }
    }
    
    function positionInColumn(isCreation) {
        if (isCreation === false)
            $("#form_module_positionInColumn").find("option")[0].remove();
        
        utility.selectSortable("#form_module_positionInColumn", null, "#form_module_sort", isCreation);
        
        $("#module_position_sort").find("i").on("click", "", function() {
            utility.selectSortable("#form_module_positionInColumn", $(this), "#form_module_sort", isCreation);
        });
        
        $("#form_module_position").on("change", "", function() {
            ajax.send(
                true,
                false,
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
                    
                    var optionSelected = $("#form_module_positionInColumn").find("option:selected");
                    
                    $("#form_module_positionInColumn").find("option").remove();
                    
                    if ($("#form_module_position").find("option:selected").index() > 0) {
                        $.each(xhr.response.values.moduleRows, function(key, value) {
                            $("#form_module_positionInColumn").append($("<option></option>").attr("value", value).text(key));
                        });
                    }
                    
                    $("#form_module_positionInColumn").append($("<option selected=\"selected\"></option>").attr("value", optionSelected.val()).text(optionSelected.text()));
                    
                    utility.selectSortable("#form_module_positionInColumn", null, "#form_module_sort", isCreation);
                },
                null,
                null
            );
        });
    }
    
    function deletion(id) {
        popupEasy.create(
            window.text.warning,
            "<p>" + window.textModule.label_1 + "</p>",
            function() {
                popupEasy.close();

                ajax.send(
                    true,
                    false,
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
                            
                            $("#form_module_selection_id").find("option[value='" + xhr.response.values.id + "']").remove();

                            $("#form_module_positionInColumn").find("option[value='" + xhr.response.values.id + "']").remove();

                            $("#cp_module_selection_result").html("");
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