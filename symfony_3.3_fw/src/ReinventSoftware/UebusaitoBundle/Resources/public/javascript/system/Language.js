/* global utility, ajax, popupEasy, loader, controlPanelPage, settings */

var language = new Language();

function Language() {
    // Vars
    var self = this;
    
    // Properties
    
    // Functions public
    self.init = function() {
        selectOnModule();
        
        $(".form_language_text").on("submit", "", function(event) {
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
                    $("#language_button").dropdown("toggle");
                    
                    if ($.isEmptyObject(xhr.response) === false && xhr.response.values !== undefined)
                        window.location.href = xhr.response.values.url;
                    else
                        ajax.reply(xhr, "#" + event.currentTarget.id);
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
                true,
                $(this).prop("action"),
                $(this).prop("method"),
                $(this).serialize(),
                "json",
                false,
                null,
                function(xhr) {
                    if ($.isEmptyObject(xhr.response) === false && xhr.response.values !== undefined) {
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
    function selectOnModule() {
        $(".form_language_codeText").on("change", "", function() {
            loader.show();
            
            $(this).parents(".form_language_text").submit();
        });
    }
    
    function selectOnPage() {
        var parameters = utility.urlParameters(settings.language);
        
        $("#language_flag").find("#language_flag_" + parameters[0]).addClass("button_flag");
        $("#language_flag").find("input[name='form_language[codePage]']").val(parameters[0]);
        
        $("#language_flag img").on("click", "", function(event) {
            if (controlPanelPage.getProfileFocus() === true) {
                popupEasy.create(
                    window.text.warning,
                    "<p>" + window.textLanguagePage.label_1 + "</p>",
                    function() {
                        popupEasy.close();

                        formPageFlagSubmit(event);
                    },
                    function() {
                        popupEasy.close();
                    }
                );
            }
            else
                formPageFlagSubmit(event);
        });
    }
    
    function formPageFlagSubmit(event) {
        controlPanelPage.setProfileFocus(false);
        
        $("#language_flag").children().removeClass("button_flag");
        $(event.target).addClass("button_flag");

        var alt = $(event.target).prop("alt");
        var altSplit = alt.split(".");

        $("#form_language_page").find("input[name='form_language[codePage]']").val(altSplit[0]);

        $("#form_language_page").submit();
    }
}