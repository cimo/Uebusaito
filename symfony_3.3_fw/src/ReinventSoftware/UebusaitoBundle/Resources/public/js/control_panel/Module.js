/* global utility, ajax, popupEasy */

var controlPanelModule = new ControlPanelModule();

function ControlPanelModule() {
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
        utility.sortableButtonModules("#module_profile_sort", new Array(
            '#form_module_position',
            '#form_module_sort'
        ));
    }
    
    function selection() {
        var table = new Table();
        table.setButtonsStatus("show");
        table.init(window.url.cpModulesSelection, "#cp_modules_selection_desktop_result", true);
        table.search(true);
        table.pagination(true);
        table.sort(true);
        
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
                    
                    table.populate(xhr);
                },
                null,
                null
            );
        });
        
        $(document).on("click", "#cp_modules_selection_desktop_result .delete_all", function() {
            popupEasy.create(
                window.text.warning,
                window.textModule.deleteAllModules,
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
    }
    
    function deletion(id) {
        popupEasy.create(
            window.text.warning,
            window.textModule.deleteModule,
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
    
    function resetView() {
        widthType = utility.checkWidth(992);
        
        if ((widthType === "desktop" || widthType === "mobile") && widthTypeOld !== widthType) {
            $("#modules_drag_switch").bootstrapSwitch("state", false, true);
            utility.sortableDragModules(false, "#form_modules_drag_sort");
            
            $("#cp_modules_selection_desktop_result").find(".checkbox_column input[type='checkbox']").prop("checked", false);
            $("#cp_modules_selection_mobile").find("select").val("");
            $("#cp_module_selection_result").html("");
            
            widthTypeOld = widthType;
        }
    }
}