var widgetSearch = new WidgetSearch();

function WidgetSearch() {
    // Vars
    var self = this;
    
    // Properties
    
    // Functions public
    self.init = function() {
        var widgetSearchButtonOpen = $(".widget_search").find(".button_open");
        var widgetSearchButtonClose = $(".widget_search").find(".button_close");
        var widgetSearchButtonInput = $(".widget_search").find("input");
        var toolbarSectionStart = $(".mdc-toolbar__section--align-start");

        $(widgetSearchButtonOpen).on("click", "", function(event) {
            var target = event.target;

            if ($(target).hasClass("animate") === false) {
                $(target).addClass("animate");
                $(widgetSearchButtonClose).show();
                $(widgetSearchButtonInput).show();
                $(toolbarSectionStart[0]).hide();
            }
            
            // Ajax search here
        });

        $(widgetSearchButtonClose).on("click", "", function(event) {
            var target = event.target;

            if ($(widgetSearchButtonOpen).hasClass("animate") === true) {
                $(target).hide();
                $(widgetSearchButtonOpen).removeClass("animate");
                widgetSearchButtonInput.val("");
                $(widgetSearchButtonInput).hide();
                $(toolbarSectionStart[0]).css("display", "inline-flex");
            }
        });
    };

    // Functions private
}