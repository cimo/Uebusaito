/* global utility, ajax, popupEasy */

var controlPanelPayment = new ControlPanelPayment();

function ControlPanelPayment() {
    // Vars
    var self = this;
    
    var selectionSended = false;
    var selectionId = -1;
    
    // Properties
    
    // Functions public
    self.init = function() {
        selection();
    };
    
    self.changeView = function() {
        if (utility.getWidthType() === "desktop") {
            if (selectionSended === true) {
                selectionId = $("#cp_payments_selection_mobile").find("select option:selected").val();

                selectionSended = false;
            }

            if (selectionId >= 0) {
                $("#cp_payments_selection_desktop_result").find(".checkbox_column input[type='checkbox']").prop("checked", false);

                var idColumns = $("#cp_payments_selection_desktop_result").find(".checkbox_column input[type='checkbox']").parents("tr").find(".id_column");

                $.each(idColumns, function(key, value) {
                    if ($(value).text().trim() === String(selectionId))
                        $(value).parents("tr").find(".checkbox_column input").prop("checked", true);
                });
            }
        }
        else {
            if (selectionSended === true) {
                selectionId = $("#cp_payments_selection_desktop_result").find(".checkbox_column input[type='checkbox']:checked").parents("tr").find(".id_column").text().trim();

                selectionSended = false;
            }

            if (selectionId > 0)
                $("#cp_payments_selection_mobile").find("select option[value='" + selectionId + "']").prop("selected", true);
        }
    };
    
    // Function private
    function selection() {
        var tableAndPagination = new TableAndPagination();
        tableAndPagination.setButtonsStatus("show");
        tableAndPagination.init(window.url.cpPaymentsSelection, "#cp_payments_selection_desktop_result", true);
        tableAndPagination.search(true);
        tableAndPagination.pagination(true);
        tableAndPagination.sort(true);
        
        $(document).on("click", "#cp_payments_selection_desktop_result .refresh", function() {
            ajax.send(
                true,
                false,
                window.url.cpPaymentsSelection,
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
        
        $(document).on("click", "#cp_payments_selection_desktop_result .delete_all", function() {
            popupEasy.create(
                window.text.warning,
                "<p>" + window.textPayment.label_2 + "</p>",
                function() {
                    popupEasy.close();
                    
                    ajax.send(
                        true,
                        false,
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
        
        $(document).on("click", "#cp_payments_selection_desktop_result .cp_payment_deletion", function() {
            var id = $.trim($(this).parents("tr").find(".id_column_hide").text());
            
            deletion(id);
        });
        
        $("#form_cp_payments_user_selection").on("submit", "", function(event) {
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
                    $("#cp_payment_selection_result").html("");
                },
                function(xhr) {
                    if ($.isEmptyObject(xhr.response) === false && xhr.response.messages.success !== undefined)
                        location.reload();
                    else
                        ajax.reply(xhr, "");
                },
                null,
                null
            );
        });
        
        $("#cp_payments_selection_send").on("click", "", function(event) {
            var id = $.trim($("#cp_payments_selection_desktop").find(".checkbox_column input:checked").parents("tr").find(".id_column_hide").text());

            ajax.send(
                true,
                true,
                window.url.cpPaymentsSelection,
                "post",
                {
                    'id': id,
                    'token': window.session.token
                },
                "json",
                false,
                function() {
                    $("#cp_payment_selection_result").html("");
                },
                function(xhr) {
                    selectionResult(xhr, "#" + event.currentTarget.id);
                },
                null,
                null
            );
        });
        
        $("#form_cp_payments_selection").on("submit", "", function(event) {
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
                    $("#cp_payment_selection_result").html("");
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
            
            $("#cp_payment_selection_result").html(xhr.response.render);
            
            $("#cp_payment_deletion").on("click", "", function() {
               deletion(xhr.urlExtra);
            });
        }
    }
    
    function deletion(id) {
        popupEasy.create(
            window.text.warning,
            "<p>" + window.textPayment.label_1 + "</p>",
            function() {
                popupEasy.close();

                ajax.send(
                    true,
                    false,
                    window.url.cpPaymentDeletion,
                    "post",
                    {
                        'event': "delete",
                        'id': id,
                        'token': window.session.token
                    },
                    "json",
                    false,
                    function() {
                        $("#cp_payment_selection_result").html("");
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