"use strict";

/* global helper, ajax, uploadChunk, materialDesign, popupEasy, chaato, widgetDatePicker */

const controlPanelApiBasic = new ControlPanelApiBasic();

function ControlPanelApiBasic() {
    // Vars
    const self = this;
    
    // Properties
    
    // Functions public
    self.init = function() {
    };
    
    self.action = function() {
        $(document).on("submit", "#form_cp_apiBasic_select", function(event) {
            event.preventDefault();

            ajax.send(
                true,
                $(this).prop("action"),
                $(this).prop("method"),
                helper.serializeJson($(this)),
                "json",
                false,
                true,
                "application/x-www-form-urlencoded; charset=UTF-8",
                function() {
                    $("#cp_api_select_result").html("");
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
            
            let optionCount = $("#form_apiBasic_select_id").find("option").length;
            let name = $(this).find("input[name='form_apiBasic[name]']").val();
            
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
            $("#cp_api_select_result").html(xhr.response.render);
            
            uploadChunk.setUrlRequest(window.url.cpApiBasicCsv + "?token=" + window.session.token + "&event=csv");
            uploadChunk.setTagContainer("#upload_chunk_apiBasic_csv_container");
            uploadChunk.setTagProgressBar("#upload_chunk_apiBasic_csv_container .upload_chunk .mdc-linear-progress");
            uploadChunk.setLockUrl(window.url.root + "/listener/lockListener.php");
            uploadChunk.processFile();
            
            widgetDatePicker.setInputFill(".widget_datePicker_input");
            widgetDatePicker.action();
            
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
                        'year': $(".graph_period_year").val(),
                        'month': $(".graph_period_month").val(),
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
                                "<p>Show graph</p>" + xhr.response.values.selectPeriodYearHtml + xhr.response.values.selectPeriodMonthHtml,
                                xhr.response.render
                            );
                    
                            $(".graph_period_year, .graph_period_month").on("change", "", function() {
                                $("#button_apiBasic_show_graph").click();
                            });
                            
                            chaato.setBackgroundType("grid"); // grid - lineX - lineY
                            chaato.setAnimationSpeed(0.50);
                            chaato.setPadding(30);
                            chaato.setTranslate([95, 20]);
                            chaato.setScale([0.91, 0.88]);
                            chaato.create(xhr.response.values.json);
                        }
                    },
                    null,
                    null
                );
            });
            
            $("#download_detail_button").on("click", "", function(event) {
                $(".download_detail_container").toggle("slow");
                
                $("#button_apiBasic_download_detail").off("click").on("click", "", function(event) {
                    let dataEvent = $(this).attr("data-event");
                    let dateStart = $("input[name='download_date_start']").val();
                    let dateEnd = $("input[name='download_date_end']").val();
                    
                    ajax.send(
                        true,
                        window.url.cpApiBasicDownloadDetail,
                        "post",
                        {
                            'event': dataEvent,
                            'dateStart': dateStart,
                            'dateEnd': dateEnd,
                            'token': window.session.token
                        },
                        "json",
                        false,
                        true,
                        "application/x-www-form-urlencoded; charset=UTF-8",
                        null,
                        function(xhr) {
                            ajax.reply(xhr, "");
                            
                            if (xhr.response.values !== undefined) {
                                let xhrRequest = new XMLHttpRequest();
                                xhrRequest.onreadystatechange = function() {
                                    if (this.readyState === 4) {
                                        window.location = xhr.response.values.url;
                                        
                                        setTimeout(function() {
                                            ajax.send(
                                                false,
                                                window.url.cpApiBasicDownloadDetail,
                                                "post",
                                                {
                                                    'event': "download_delete",
                                                    'token': window.session.token
                                                },
                                                "json",
                                                false,
                                                true,
                                                "application/x-www-form-urlencoded; charset=UTF-8",
                                                null,
                                                function(xhr) {
                                                    ajax.reply(xhr, "");
                                                },
                                                null,
                                                null
                                            );
                                        }, 100);
                                    }
                                };
                                xhrRequest.open("head", xhr.response.values.url, true);
                                xhrRequest.send();
                            }
                        },
                        null,
                        null
                    );
                });
            });
            
            $("#form_cp_apiBasic_profile").on("submit", "", function(event) {
                event.preventDefault();
                
                let selectValue = $("#form_apiBasic_select_id").val();
                let name = $(this).find("input[name='form_apiBasic[name]']").val();

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
                            
                            $("#cp_api_select_result").html("");
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

                                    $("#cp_api_select_result").html("");
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