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
        
        selection();
        
        $("#form_cp_modules_drag").on("submit", "", function(event) {
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
        $("#modules_drag_switch").bootstrapSwitch("state", false, true);
        utility.sortableDragModules(false, "#form_modules_drag_sort");

        if (utility.getWidthType() === "desktop") {
            if (selectionSended === true) {
                selectionId = $("#cp_modules_selection_mobile").find("select option:selected").val();

                selectionSended = false;
            }

            if (selectionId >= 0) {
                $("#cp_modules_selection_desktop_result").find(".checkbox_column input[type='checkbox']").prop("checked", false);

                var idColumns = $("#cp_modules_selection_desktop_result").find(".checkbox_column input[type='checkbox']").parents("tr").find(".id_column");

                $.each(idColumns, function(key, value) {
                    if ($(value).text().trim() === String(selectionId))
                        $(value).parents("tr").find(".checkbox_column input").prop("checked", true);
                });
            }
        }
        else {
            if (selectionSended === true) {
                selectionId = $("#cp_modules_selection_desktop_result").find(".checkbox_column input[type='checkbox']:checked").parents("tr").find(".id_column").text().trim();

                selectionSended = false;
            }

            if (selectionId > 0)
                $("#cp_modules_selection_mobile").find("select option[value='" + selectionId + "']").prop("selected", true);
        }
    };
    
    // Function private
    function sortableDrag() {
        $("#modules_drag_switch").bootstrapSwitch("state", false);
        
        $("#modules_drag_switch").on("switchChange.bootstrapSwitch", "", function(event, state) {
            utility.sortableDragModules(state, "#form_modules_drag_sort");
            
            if (state === false)
                $("#form_cp_modules_drag").submit();
        });
    }
    
    function sortableButton() {
        utility.selectSortableWithCheckbox("#module_profile_sort", new Array(
            '#form_module_position',
            '#form_module_sort'
        ));
    }
    
    function selection() {
        var tableAndPagination = new TableAndPagination();
        tableAndPagination.setButtonsStatus("show");
        tableAndPagination.init(window.url.cpModulesSelection, "#cp_modules_selection_desktop_result", true);
        tableAndPagination.search(true);
        tableAndPagination.pagination(true);
        tableAndPagination.sort(true);
        
        $(document).on("click", "#cp_modules_selection_desktop_result .refresh", function() {
            ajax.send(
                true,
                false,
                window.url.cpModulesSelection,
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
        
        $(document).on("click", "#cp_modules_selection_desktop_result .delete_all", function() {
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
        
        $(document).on("click", "#cp_modules_selection_desktop_result .cp_module_deletion", function() {
            var id = $.trim($(this).parents("tr").find(".id_column").text());
            
            deletion(id);
        });
        
        $("#cp_modules_selection_send").on("click", "", function(event) {
            var id = $.trim($("#cp_modules_selection_desktop_result").find(".checkbox_column input:checked").parents("tr").find(".id_column").text());

            ajax.send(
                true,
                true,
                window.url.cpModulesSelection,
                "post",
                {
                    'id': id,
                    'token': window.session.token
                },
                "json",
                false,
                function() {
                    $("#cp_module_selection_result").html("");
                },
                function(xhr) {
                    selectionResult(xhr, "#" + event.currentTarget.id);
                },
                null,
                null
            );
        });

        $("#form_cp_modules_selection").on("submit", "", function(event) {
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
            
            $("#cp_module_selection_result").html(xhr.response.render);

            profile(xhr);

            $("#cp_module_deletion").on("click", "", function() {
               deletion(xhr.urlExtra);
            });
        }
    }
    
    function profile(xhr) {
        var sort = "";

        $.each(xhr.response.values.moduleRows, function(key, value) {
            sort += value.id + ",";
        });

        sort = sort.substring(0, sort.length - 1);

        $("#form_module_sort").val(sort);
        
        sortableButton();
        
        utility.selectOnlyOneElement("#module_profile_sort");
        
        $("#form_module_position").on("change", "", function() {
            var position = $(this).val();
            
            ajax.send(
                true,
                true,
                window.url.cpModuleProfileSort,
                "post",
                {
                    'id': xhr.urlExtra,
                    'position': position,
                    'token': window.session.token
                },
                "json",
                false,
                null,
                function(xhr) {
                    if ($.isEmptyObject(xhr.response) === false && xhr.response.render !== undefined) {
                        $("#module_profile_sort").html(xhr.response.render);
                        
                        sortableButton();
                    }
                    else
                        ajax.reply(xhr, "");
                },
                null,
                null
            );
        });
        
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
                },
                null,
                null
            );
        });
        
        var selected = $("#form_modules_selection_id").find(":selected").val();
        
        if ($("#panel_id_" + selected).parent().hasClass("settings_hide") === true) {
            $("#form_module_position").parents(".form-group").hide();
            $("#module_profile_sort").hide();
        }
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
                    function() {
                        $("#cp_module_selection_result").html("");
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