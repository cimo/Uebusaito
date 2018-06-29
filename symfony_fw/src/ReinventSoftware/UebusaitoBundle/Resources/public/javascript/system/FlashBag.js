// Version 1.0.0

var flashBag = new FlashBag();

function FlashBag() {
    // Vars
    var self = this;
    
    var element;
    
    // Properties
    self.setElement = function(value) {
        element = value;
    }
    
    // Functions public
    self.init = function() {
        element = null;
    }
    
    self.show = function(message) {
        var snackbarDataObj = {
            message: message,
            actionText: window.text.close,
            actionHandler: function() {}
        };
        
        element.show(snackbarDataObj);
    };
    
    self.sessionActivity = function() {
        if ($("#flashBag").find(".content").html() !== undefined && $("#flashBag").find(".content").html().trim() === "" && window.session.userActivity !== "")
            self.show(window.session.userActivity);
    };
    
    // Functions private
}