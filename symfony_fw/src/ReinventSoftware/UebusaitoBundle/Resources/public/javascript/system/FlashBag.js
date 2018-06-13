// Version 1.0.0

var flashBag = new FlashBag();

function FlashBag() {
    // Vars
    var self = this;
    
    var element;
    var message;
    
    // Properties
    self.setElement = function(value) {
        element = value;
    }
    
    self.setMessage = function(value) {
        message = value;
    }
    
    // Functions public
    self.init = function() {
        element = null;
        message = "";
    }
    
    self.show = function() {
        var snackbarDataObj = {
            message: message,
            actionText: window.text.close,
            actionHandler: function() {}
        };
        
        element.show(snackbarDataObj);
    };
    
    self.sessionActivity = function() {
        if ($("#flashBag").find(".content").html() !== undefined && $("#flashBag").find(".content").html().trim() === "" && window.session.userActivity !== "")
            self.show();
    };
    
    // Functions private
}