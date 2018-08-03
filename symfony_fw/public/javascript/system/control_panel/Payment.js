/* global utility, ajax, popupEasy, materialDesign */

var controlPanelPayment = new ControlPanelPayment();

function ControlPanelPayment() {
    // Vars
    var self = this;
    
    var selectionSended = false;
    var selectionId = -1;
    
    // Properties
    
    // Functions public
    self.init = function() {
        $("#form_cp_payment_user_selection").on("submit", "", function(event) {
            event.preventDefault();
            
            ajax.send(
                true,
                $(this).prop("action"),
                $(this).prop("method"),
                $(this).serialize(),
                "json",
                false,
                function() {
                    $("#cp_payment_selection_result").html("");
                },
                function(xhr) {
                    if (xhr.response.messages.success !== undefined)
                        location.reload();
                    else
                        ajax.reply(xhr, "");
                },
                null,
                null
            );
        });
        
        selectionDesktop();
        
        selectionMobile();
    };
    
    self.changeView = function() {
        if (utility.checkWidthType() === "mobile") {
            if (selectionSended === true) {
                selectionId = $("#cp_payment_selection_mobile").find("select option:selected").val();

                selectionSended = false;
            }

            if (selectionId >= 0) {
                $("#cp_payment_selection_result_desktop").find(".checkbox_column input[type='checkbox']").prop("checked", false);

                var idColumns = $("#cp_payment_selection_result_desktop").find(".checkbox_column input[type='checkbox']").parents("tr").find(".id_column");

                $.each(idColumns, function(key, value) {
                    if ($(value).text().trim() === String(selectionId))
                        $(value).parents("tr").find(".checkbox_column input").prop("checked", true);
                });
            }
        }
        else {
            if (selectionSended === true) {
                selectionId = $("#cp_payment_selection_result_desktop").find(".checkbox_column input[type='checkbox']:checked").parents("tr").find(".id_column").text().trim();

                selectionSended = false;
            }

            if (selectionId > 0)
                $("#cp_payment_selection_mobile").find("select option[value='" + selectionId + "']").prop("selected", true);
        }
    };
    
    // Function private
    function selectionDesktop() {
        if ($("#cp_payment_selection_result_desktop").find("table tbody td").length > 0)
            $(".button_accordion").eq(1).click();
        
        var tableAndPagination = new TableAndPagination();
        tableAndPagination.init();
        tableAndPagination.setButtonsStatus("show");
        tableAndPagination.create(window.url.cpPaymentSelection, "#cp_payment_selection_result_desktop", true);
        tableAndPagination.search();
        tableAndPagination.pagination();
        tableAndPagination.sort();
        
        $(document).on("click", "#cp_payment_selection_result_desktop .refresh", function() {
            ajax.send(
                true,
                window.url.cpPaymentSelection,
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
        
        $(document).on("click", "#cp_payment_selection_result_desktop .delete_all", function() {
            popupEasy.create(
                window.text.index_5,
                window.textPayment.label_2,
                function() {
                    ajax.send(
                        true,
                        window.url.cpPaymentDeletion,
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

                            $.each($("#cp_payment_selection_result_desktop").find("table .id_column"), function(key, value) {
                                $(value).parents("tr").remove();
                            });
                            
                            $("#cp_payment_selection_result").html("");
                        },
                        null,
                        null
                    );
                }
            );
        });
        
        $(document).on("click", "#cp_payment_selection_result_desktop .cp_payment_deletion", function() {
            var id = $.trim($(this).parents("tr").find(".id_column").text());
            
            deletion(id);
        });
        
        $(document).on("click", "#cp_payment_selection_button_desktop", function(event) {
            var id = $.trim($(this).parent().find(".checkbox_column input:checked").parents("tr").find(".id_column").text());

            ajax.send(
                true,
                window.url.cpPaymentProfileResult,
                "post",
                {
                    'event': "result",
                    'id': id,
                    'token': window.session.token
                },
                "json",
                false,
                function() {
                    $("#cp_payment_selection_result").html("");
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
        $(document).on("submit", "#form_cp_payment_selection_mobile", function(event) {
            event.preventDefault();

            ajax.send(
                true,
                $(this).prop("action"),
                $(this).prop("method"),
                $(this).serialize(),
                "json",
                false,
                function() {
                    $("#cp_payment_selection_result").html("");
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
            
            $("#cp_payment_selection_result").html(xhr.response.render);
            
            materialDesign.refresh();
            
            $("#cp_payment_deletion").on("click", "", function() {
               deletion(null);
            });
        }
    }
    
    function deletion(id) {
        popupEasy.create(
            window.text.index_5,
            window.textPayment.label_1,
            function() {
                ajax.send(
                    true,
                    window.url.cpPaymentDeletion,
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
                            $.each($("#cp_payment_selection_result_desktop").find("table .id_column"), function(key, value) {
                                if (xhr.response.values.id === $.trim($(value).text()))
                                    $(value).parents("tr").remove();
                            });

                            $("#form_payment_selection_id").find("option[value='" + xhr.response.values.id + "']").remove();

                            $("#cp_payment_selection_result").html("");
                        }
                    },
                    null,
                    null
                );
            }
        );
    }
}