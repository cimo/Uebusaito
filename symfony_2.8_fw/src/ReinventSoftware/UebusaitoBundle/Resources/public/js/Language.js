/* global utility, ajax, popupEasy, controlPanelPage, url */

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
                false,
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
                false,
                $(this).prop("action"),
                $(this).prop("method"),
                $(this).serialize(),
                "json",
                false,
                null,
                function(xhr) {
                    if ($.isEmptyObject(xhr.response) === false && xhr.response.values !== undefined) {
                        $("#form_cp_page_profile").find("input[name='form_page[language]']").val(xhr.response.values.codePage);
                        $("#form_cp_page_profile").find("input[name='form_page[title]']").val(xhr.response.values.pageFields.title);
                        $("#form_cp_page_profile").find(".jqte_editor").html(xhr.response.values.pageFields.argument);
                        $("#form_cp_page_profile").find("textarea[name='form_page[argument]']").val(xhr.response.values.pageFields.argument);
                        $("#form_cp_page_profile").find("input[name='form_page[menuName]']").val(xhr.response.values.pageFields.menu_name);
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
            $(this).parents(".form_language_text").submit();
        });
    }
    
    function selectOnPage() {
        var parameters = utility.urlParameters(settings.language);
        
        $("#language_flag").find("#language_flag_" + parameters[0]).addClass("language_page_flag_selected");
        $("#language_flag").find("input[name='form_language[codePage]']").val(parameters[0]);
        
        $("#language_flag img").on("click", "", function(event) {
            if (controlPanelPage.getProfileFocus() === true) {
                popupEasy.create(window.text.warning,
                    window.text.changePageLanguage,
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
        
        $("#language_flag").children().removeClass("language_page_flag_selected");
        $(event.target).addClass("language_page_flag_selected");

        var alt = $(event.target).prop("alt");
        var altSplit = alt.split(".");

        $("#form_language_page").find("input[name='form_language[codePage]']").val(altSplit[0]);

        $("#form_language_page").submit();
    }
}