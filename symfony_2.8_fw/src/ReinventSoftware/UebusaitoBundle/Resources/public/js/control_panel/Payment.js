/* global utility, ajax, popupEasy */

var controlPanelPayment = new ControlPanelPayment();

function ControlPanelPayment() {
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
        
        selection();
    };
    
    // Function private
    function selection() {
        var table = new Table();
        table.setButtonsStatus("show");
        table.init(window.url.cpPaymentsSelection, "#cp_payments_selection_desktop_result");
        table.search(true);
        table.pagination(true);
        table.sort(true);
        
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
                    
                    table.populate(xhr);
                },
                null,
                null
            );
        });
        
        $(document).on("click", "#cp_payments_selection_desktop_result .delete_all", function() {
            popupEasy.create(
                window.text.warning,
                window.text.deleteAllPayments,
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
        
        $(document).on("click", ".cp_payment_deletion", function() {
            var id = $.trim($(this).parents("tr").find(".id_column_hide").text());
            
            deletion(id);
        });
        
        $("#form_cp_payments_user_selection").on("submit", "", function(event) {
            event.preventDefault();
            
            ajax.send(
                true,
                false,
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
                false,
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
                false,
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
            $("#cp_payment_selection_result").html(xhr.response.render);
            
            $("#cp_payment_deletion").on("click", "", function() {
               deletion(xhr.urlExtra);
            });
        }
    }
    
    function deletion(id) {
        popupEasy.create(
            window.text.warning,
            window.text.deletePayment,
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
    
    function resetView() {
        widthType = utility.widthCheck(992);
        
        if ((widthType === "desktop" || widthType === "mobile") && widthTypeOld !== widthType) {
            $("#cp_payments_selection_desktop").find(".checkbox_column input[type='checkbox']").prop("checked", false);
            $("#cp_payments_selection_mobile").find("select").val("");
            $("#cp_payment_selection_result").html("");
            
            widthTypeOld = widthType;
        }
    }
}