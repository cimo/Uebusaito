var kasutamuMono2d = new KasutamuMono2d();
kasutamuMono2d.init();
kasutamuMono2d.create();

function KasutamuMono2d() {
    // Vars
    var self = this;
    
    var jsonResult;
    
    var canvas;
    var context;
        
    var isLoading;
    
    // Properties
    
    // Functions public
    self.init = function() {
        jsonResult = {};
        
        canvas = $(".custom_product .canvas");
        context = canvas[0].getContext("2d");
        
        isLoading = loading(true);
    };
    
    self.create = function() {
        $(document).on("click", ".container_main_color .dot_color", function() {
            if (isLoading === true)
                return;
            
            selected(this, ".dot_color");
            
            addMain();
        });
        
        $(document).on("click", ".container_main_label .dot_color", function() {
            if (isLoading === true)
                return;
            
            selected(this, ".dot_color");
            
            addMain();
        });
        
        $(document).on("click", ".container_accessory .accessory", function() {
            if (isLoading === true)
                return;
            
            selected(this, ".accessory");
            
            addMain();
        });
        
        $(".container_main").find(".preview_save").on("click", "", function() {
            loading(true);
            
            $.ajax({
                type: "post",
                url: "https://ls1.reinventsoftware.org/uebusaito/symfony_fw/public/kasutamu_mono_2d_api_preview",
                data: { 
                    imageBase64: canvas[0].toDataURL()
                },
                success: function(xhr) {
                    loading(false);
                    
                    $(".result_preview").html(xhr);
                    
                    if (xhr.response.values.id !== undefined) {
                        $("#kasutamu_mono_2d_preview_image").show();
                        $("#kasutamu_mono_2d_preview_image").prop("src", "https://ls1.reinventsoftware.org/uebusaito/symfony_fw/public/microservice/api/kasutamu_mono_2d/preview_customization/" + xhr.response.values.id + ".jpg");
                    }
                }
            });
        });
        
        readJson(function(json) {
            jsonResult = json;
            
            createMainColorHtml();
            
            createMainLabelHtml();
            
            createAccessoryHtml();
            
            addMain();
        });
    };
    
    // Functions private
    function readJson(callback) {
        $.getJSON("https://local-al-t-dev.jp/upload/source.json", function(result) {
            callback(result);
        });
    }
    
    function loading(type) {
        if (type === true) {
            isLoading = true;
            
            $(".custom_product .loading_canvas").show();
        }
        else {
            isLoading = false;
            
            $(".custom_product .loading_canvas").hide();
        }
    }
    
    function loadImage(src, x, y, w, h, callback) {
        loading(true);
        
        var image = new Image();
        image.setAttribute("crossOrigin", "Anonymous");
        image.src = src;
        image.onload = function() {
            width = w === undefined ? image.width : w;
            height = h === undefined ? image.height : h;
            
            context.drawImage(image, 0, 0, image.width, image.height, x, y, width, height);
            
            loading(false);
            
            if (callback !== undefined)
                callback();
        };
    }
    
    function selected(target, tag) {
        $(target).parent().find(tag).removeClass("selected");
        $(target).addClass("selected");
    }
    
    function createMainColorHtml() {
        $.each(jsonResult.main.colors, function(key, value) {
            $(".container_main_color").append("<div class=\"dot_color\" style=\"background-color: " + value + "\"></div>");
        });
    }
    
    function createMainLabelHtml() {
        var html = "<p>Label:</p>";
        
        $.each(jsonResult.label.coordinates, function(key, value) {
            html += "<input name=\"label_coordinate_" + key + "\" type=\"text\" value=\"\">";
        });
        
        $.each(jsonResult.label.colors, function(key, value) {
            html += "<div class=\"dot_color\" style=\"background-color: " + value + "\"></div>";
        });
        
        $(".container_main_label").append(html);
    }
    
    function createAccessoryHtml() {
        $.each(jsonResult.accessories, function(key, value) {
            $(".container_accessory").append("<img class=\"accessory\" src=\"" + value.path + "\"/>");
        });
    }
    
    function addMain() {
        var index = $(".container_main_color").find(".dot_color.selected").index();
        
        if (index === -1)
            index = 0;
        
        var imageMainPath = jsonResult.main.paths[index];
        
        loadImage(imageMainPath, 0, 0, 500, 600, function() {
            addText();
            
            addAccessory();
        });
    }
    
    function addText() {
        var color = $(".container_main_label").find(".dot_color.selected");
        
        if (color.length === 0)
            color = $(".container_main_label").find(".dot_color").eq(0);
        
        context.font = "20pt Calibri";
        context.fillStyle = color.css("background-color");
        context.textAlign = "center";
        
        $.each(jsonResult.label.coordinates, function(key, value) {
            var coordinates = value.split(",");
            
            context.fillText($("input[name='label_coordinate_" + key + "']").val().toUpperCase(), (canvas[0].width / 2) + parseInt(coordinates[0]), (canvas[0].height / 2) + parseInt(coordinates[1]));
        });
    }
    
    function addAccessory() {
        var index = $(".container_accessory").find(".accessory.selected").index();
        
        if (index !== -1) {
            var accessory = jsonResult.accessories[index];
            
            var coordinates = accessory.coordinates.split(",");
            
            loadImage(accessory.path, coordinates[0], coordinates[1], 200, 260);
        }
    }
}