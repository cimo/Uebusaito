/* global utility, ajax, popupEasy, loader, controlPanelPage, wysiwyg */

var language = new Language();

function Language() {
    // Vars
    var self = this;
    
    // Properties
    
    // Functions public
    self.init = function() {
        //selectOnModule();
        
        /*$("#form_language_text").on("submit", "", function(event) {
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
                    $("#language_button").dropdown("toggle");
                    
                    if ($.isEmptyObject(xhr.response) === false && xhr.response.values !== undefined)
                        window.location.href = xhr.response.values.url;
                    else
                        ajax.reply(xhr, "#" + event.currentTarget.id);
                },
                null,
                null
            );
        });*/
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
                null,
                function(xhr) {
                    if ($.isEmptyObject(xhr.response) === false && xhr.response.values !== undefined) {
                        wysiwyg.historyClear();
                        
                        $("#form_cp_page_profile").find("input[name='form_page[language]']").val(xhr.response.values.codePage);
                        $("#form_cp_page_profile").find("input[name='form_page[title]']").val(xhr.response.values.pageTitle);
                        $("#wysiwyg").find(".editor").contents().find("body").html(xhr.response.values.pageArgument);
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
    /*function selectOnModule() {
        $(".form_language_codeText").on("change", "", function() {
            loader.show();
            
            $(this).parents(".form_language_text").submit();
        });
    }*/
    
    function selectOnPage() {
        $("#language_page_container").find(".flag_" + window.setting.language).parent().addClass("mdc-chip--selected");
        $("#language_page_container").find("input[name='form_language[codePage]']").val(window.setting.language);
        
        $("#language_page_container .mdc-chip").on("click", "", function(event) {
            if (controlPanelPage.getProfileFocus() === true) {
                popupEasy.create(
                    window.text.warning,
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