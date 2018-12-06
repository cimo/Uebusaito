/* global ajax, popupEasy, controlPanelPage, wysiwyg */

var language = new Language();

function Language() {
    // Vars
    var self = this;
    
    // Properties
    
    // Functions public
    self.init = function() {
        $("#language_text_container").find(".mdc-list-item").on("click", "", function(event) {
            ajax.send(
                true,
                window.url.languageText,
                "post",
                {
                    'event': "languageText",
                    'languageTextCode': $(event.target).find("img").prop("class"),
                    'token': window.session.token
                },
                "json",
                false,
                true,
                "application/x-www-form-urlencoded; charset=UTF-8",
                null,
                function(xhr) {
                    if ($.isEmptyObject(xhr.response) === false && xhr.response.values !== undefined)
                        window.location.href = xhr.response.values.url;
                },
                null,
                null
            );
        });
    };
    
    self.page = function() {
        selectOnPage();
        
        $("#form_language_page").on("submit", "", function(event) {
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
                null,
                function(xhr) {
                    if ($.isEmptyObject(xhr.response) === false && xhr.response.values !== undefined) {
                        wysiwyg.historyClear();
                        
                        $("#form_cp_page_profile").find("input[name='form_page[language]']").val(xhr.response.values.codePage);
                        $("#form_cp_page_profile").find("input[name='form_page[title]']").val(xhr.response.values.pageTitle);
                        $(".wysiwyg").find(".editor").contents().find("body").html(xhr.response.values.pageArgument);
                        $("#form_cp_page_profile").find("input[name='form_page[menuName]']").val(xhr.response.values.pageMenuName);
                    }
                    else
                        ajax.reply(xhr, "#" + event.currentTarget.id);
                },
                null,
                null
            );
        });
    };
    
    // Functions private
    function selectOnPage() {
        $("#language_page_container").find(".flag_" + session.languageTextCode).parent().addClass("mdc-chip--selected");
        $("#language_page_container").find("input[name='form_language[codePage]']").val(session.languageTextCode);
        
        $("#language_page_container .mdc-chip").on("click", "", function(event) {
            if (controlPanelPage.getProfileFocus() === true) {
                popupEasy.create(
                    window.text.index_1,
                    window.textLanguagePage.label_1,
                    function() {
                        formPageFlagSubmit(event);
                    }
                );
            }
            else
                formPageFlagSubmit(event);
        });
    }
    
    function formPageFlagSubmit(event) {
        controlPanelPage.setProfileFocus(false);
        
        var target = $(event.target).parent().hasClass("mdc-chip") === true ? $(event.target).parent() : $(event.target);
        
        $("#language_page_container").children().removeClass("mdc-chip--selected");
        target.addClass("mdc-chip--selected");
        
        var altSplit = target.find("img").prop("alt").split(".");

        $("#form_language_page").find("input[name='form_language[codePage]']").val(altSplit[0]);
        $("#form_language_page").submit();
    }
}