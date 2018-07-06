/* global materialDesign */

var popupEasy = new PopupEasy();

function PopupEasy() {
    // Vars
    var self = this;
    
    var dialogMdc = null;
    
    // Properties
    
    // Functions public
    self.create = function(title, message, callbackOk, callbackCancel) {
        $(".mdc-dialog").find(".mdc-dialog__header__title").html(title);
        $(".mdc-dialog").find(".mdc-dialog__body").html(message);
        $(".mdc-dialog").find(".mdc-dialog__footer__button--accept").text(window.text.ok);
        $(".mdc-dialog").find(".mdc-dialog__footer__button--cancel").text(window.text.cancel);
        
        var clickOk = null;
        var clickCancel = null;
        
        if (callbackOk !== undefined) {
            clickOk = function() {
                callbackOk();
            };
            
            $(".mdc-dialog").find(".mdc-dialog__footer__button--accept").on("click", "", clickOk);
        }
        
        if (callbackCancel !== undefined) {
            clickCancel = function() {
                callbackCancel();
            };
            
            $(".mdc-dialog").find(".mdc-dialog__footer__button--cancel").on("click", "", clickCancel);
        }
        
        dialogMdc = materialDesign.getDialogMdc();
        dialogMdc.show();
    };
    
    self.close = function() {
        dialogMdc.close();
    };
    
    self.recursive = function(title, obj, key) {
        self.create(
            title,
            obj[key],
            function(){
                self.close();

                if (key + 1 < obj.length)
                    self.recursive(title, obj, key + 1);
            },
            null
        );
    };
    
    // Functions private
}