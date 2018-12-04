var controlPanelApiBasic = new ControlPanelApiBasic();

function ControlPanelApiBasic() {
    // Vars
    var self = this;
    
    var year;
    var month;
    
    // Properties
    
    // Functions public
    self.init = function() {
        year = "";
        month = "";
        
        $(document).on("submit", "#form_cp_apiBasic_select", function(event) {
            event.preventDefault();

            ajax.send(
                true,
                $(this).prop("action"),
                $(this).prop("method"),
                $(this).serialize(),
                "json",
                false,
                true,
                "application/x-www-form-urlencoded; charset=UTF-8",
                function() {
                    $("#cp_apiBasic_select_result").html("");
                },
                function(xhr) {
                    profile(xhr, "#" + event.currentTarget.id);
                },
                null,
                null
            );
        });
        
        $("#form_cp_apiBasic_create").on("submit", "", function(event) {
            event.preventDefault();
            
            var optionCount = $("#form_apiBasic_select_id").find("option").length;
            var name = $(this).find("input[name='form_apiBasic[name]']").val();
            
            ajax.send(
                true,
                $(this).prop("action"),
                $(this).prop("method"),
                $(this).serialize(),
                "json",
                false,
                true,
                "application/x-www-form-urlencoded; charset=UTF-8",
                null,
                function(xhr) {
                    ajax.reply(xhr, "#" + event.currentTarget.id);
                    
                    if (xhr.response.messages.success !== undefined)
                        $("#form_apiBasic_select_id").append("<option value=\"" + optionCount + "\">" + name + "</option>");
                },
                null,
                null
            );
        });
    };
    
    // Function private
    function profile(xhr, tag) {
        ajax.reply(xhr, tag);
        
        if ($.isEmptyObject(xhr.response) === false && xhr.response.render !== undefined) {
            $("#cp_apiBasic_select_result").html(xhr.response.render);
            
            materialDesign.refresh();
            
            $("#button_apiBasic_show_log").on("click", "", function(event) {
                ajax.send(
                    true,
                    window.url.cpApiBasicLog,
                    "post",
                    {
                        'event': "log",
                        'token': window.session.token
                    },
                    "json",
                    false,
                    true,
                    "application/x-www-form-urlencoded; charset=UTF-8",
                    null,
                    function(xhr) {
                        ajax.reply(xhr, "");

                        if (xhr.response.values.log !== undefined) {
                            popupEasy.create(
                                "File log",
                                xhr.response.values.log
                            );
                        }
                    },
                    null,
                    null
                );
            });
            
            $("#button_apiBasic_show_graph").on("click", "", function(event) {
                ajax.send(
                    true,
                    window.url.cpApiBasicGraph,
                    "post",
                    {
                        'event': "graph",
                        'year': year,
                        'month': month,
                        'token': window.session.token
                    },
                    "json",
                    false,
                    true,
                    "application/x-www-form-urlencoded; charset=UTF-8",
                    null,
                    function(xhr) {
                        ajax.reply(xhr, "");
                        
                        if (xhr.response.render !== undefined) {
                            popupEasy.create(
                                "Show graph - " + xhr.response.values.selectPeriodYearHtml + xhr.response.values.selectPeriodMonthHtml,
                                xhr.response.render
                            );
                    
                            $(".graph_period_year, .graph_period_month").on("change", "", function() {
                                year = $(".graph_period_year").val();
                                month = $(".graph_period_month").val();
                                
                                $("#button_apiBasic_show_graph").click();
                            });
                            
                            chaato.init();
                            chaato.setBackgroundType("grid"); // grid - lineX - lineY
                            chaato.setAnimationSpeed(0.50);
                            chaato.setPadding(30);
                            chaato.setTranslate(new Array(95, 20));
                            chaato.setScale(new Array(0.91, 0.88));
                            chaato.create(xhr.response.values.json);
                        }
                    },
                    null,
                    null
                );
            });

            $("#form_cp_apiBasic_profile").on("submit", "", function(event) {
                event.preventDefault();
                
                var selectValue = $("#form_apiBasic_select_id").val();
                var name = $(this).find("input[name='form_apiBasic[name]']").val();

                ajax.send(
                    true,
                    $(this).prop("action"),
                    $(this).prop("method"),
                    $(this).serialize(),
                    "json",
                    false,
                    true,
                    "application/x-www-form-urlencoded; charset=UTF-8",
                    null,
                    function(xhr) {
                        ajax.reply(xhr, "#" + event.currentTarget.id);
                        
                        if (xhr.response.messages.success !== undefined) {
                            $("#form_apiBasic_select_id").find("option[value='" + selectValue + "']").text(name);
                            
                            $("#cp_apiBasic_select_result").html("");
                        }
                    },
                    null,
                    null
                );
            });

            $("#cp_apiBasic_delete").on("click", "", function() {
               popupEasy.create(
                    window.text.index_5,
                    window.textApiBasic.label_1,
                    function() {
                        ajax.send(
                            true,
                            window.url.cpApiBasicDelete,
                            "post",
                            {
                                'event': "delete",
                                'id': null,
                                'token': window.session.token
                            },
                            "json",
                            false,
                            true,
                            "application/x-www-form-urlencoded; charset=UTF-8",
                            null,
                            function(xhr) {
                                ajax.reply(xhr, "");

                                if (xhr.response.messages.success !== undefined) {
                                    $("#form_apiBasic_select_id").find("option[value='" + xhr.response.values.id + "']").remove();

                                    $("#cp_apiBasic_select_result").html("");
                                }
                            },
                            null,
                            null
                        );
                    }
                );
            });
        }
    }
}