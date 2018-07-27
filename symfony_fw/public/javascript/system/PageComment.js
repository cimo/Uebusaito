/* global utility, ajax */

var pageComment = new PageComment();

function PageComment() {
    // Vars
    var self = this;
    
    // Properties
    
    // Functions public
    self.init = function() {
        var tableAndPagination = new TableAndPagination();
        tableAndPagination.init();
        tableAndPagination.create(window.url.pageCommentRender, "#pageComment_result", false);
        tableAndPagination.search();
        tableAndPagination.pagination();
        
        $("#form_pageComment").on("submit", "", function(event) {
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
                    
                    reset();
                },
                null,
                null
            );
        });
        
        $(document).on("click", "#pageComment_result .button_reply", function(event) {
            event.preventDefault();
            
            reset();
            
            var argument = $(event.target).parent().prev().find(".argument");
            var argumentId = argument.prop("class").replace("argument item_", "");
            
            ajax.send(
                true,
                window.url.pageCommentRender,
                "post",
                {
                    'event': "reply",
                    'id': argumentId,
                    'token': window.session.token
                },
                "json",
                false,
                null,
                function(xhr) {
                    ajax.reply(xhr, "");
                    
                    $(".username_reply").show();
                    $(".message_reply").show();
                    
                    $(".username_reply").find("span").text(xhr.response.values.usernameReply);
                    $(".message_reply").find(".argument").text(xhr.response.values.argumentReply);
                    
                    $("#form_pageComment").find("textarea[name='argument']").focus();
                    
                    utility.goToAnchor("#page_commnet_anchor");
                },
                null,
                null
            );
        });
        
        $(document).on("click", "#pageComment_result .button_modify", function(event) {
            event.preventDefault();
            
            reset();
            
            var argument = $(event.target).parent().prev().find(".argument");
            var argumentId = argument.prop("class").replace("argument item_", "");
            
            if (argument.prop("contenteditable") === "false") {
                argument.prop("contenteditable", true);
                argument.focus();

                $(event.target).text(window.textPageComment.label_1);
            }
            else {
                ajax.send(
                    true,
                    window.url.pageCommentRender,
                    "post",
                    {
                        'event': "modify",
                        'id': argumentId,
                        'content': argument.text(),
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
            }
        });
    };
    
    // Function private
    function reset() {
        $.each($(".list_result").find(".row"), function(key, value) {
            $(value).find(".message .argument").prop("contenteditable", false);
            $(value).find(".action .button_modify").text(window.textPageComment.label_2);
        });
    }
}