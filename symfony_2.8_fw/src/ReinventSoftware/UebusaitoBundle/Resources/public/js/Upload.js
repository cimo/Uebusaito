/* global utility, ajax, popupEasy */

var upload = new Upload();

function Upload() {
    // Vars
    var self = this;
    
    var inputType = "";
    var maxSize = 0;
    var chunkSize = 0;
    var extensions = new Array();
    
    var file = null;
    var tmp = 0;
    var uploadStarted = false;
    var uploadPaused = false;
    var uploadAborted = false;
    var chunkCurrent = 0;
    var chunkPause = 0;
    var timeStart = 0;
    var totalTime = 0;
    var timeLeft = 0;
    
    // Properties
    
    // Functions public
    self.processFile = function() {
        $("#upload").show();
        
        if (inputType === "multiple")
            $("#upload").find(".file").prop("multiple", "multiple");

        $(document).on("change", "#upload .file", function() {
            ajax.send(
                false,
                false,
                window.url.cpProfileUpload,
                "post",
                {},
                "json",
                false,
                null,
                function(xhr) {
                    if (xhr.response.upload !== undefined) {
                        inputType = xhr.response.upload.inputType;
                        maxSize = xhr.response.upload.maxSize;
                        chunkSize = xhr.response.upload.chunkSize;
                        extensions = xhr.response.upload.extensions;
                        
                        file = $("#upload").find(".file")[0].files[0];

                        if (file.size > maxSize || extensions === false) {
                            resetValue("hide");

                            message(true, window.text.uploadTextD);

                            return;
                        }

                        if (file !== null)
                            resetValue("show");
                        else
                            resetValue("hide");
                        
                        $(document).on("click", "#upload .button_1 span", function() {
                            if (inputType === "single" && $("#upload").find(".file").prop("multiple") === true)
                                return;

                            if (uploadStarted === false && uploadPaused === false)
                                start();
                            else if (uploadStarted === true && uploadPaused === false)
                                pause();
                            else if (uploadStarted === true && uploadPaused === true)
                                resume();
                        });

                        $(document).on("click", "#upload .button_2 span", function() {
                            abort();
                        });
                    }
                },
                null,
                null
            );
        });

        $(document).on("click", "#upload_text_close", function() {
            message(false, "");
        });
    };
    
    // Functions private
    function start() {
        if (file !== null) {
            uploadStarted = true;
            
            message(true, window.text.uploadTextB);
            
            $("#upload").find(".button_1 span").text(window.text.uploadButtonB);
            
            chunkCurrent = Math.ceil(file.size / chunkSize);

            sendChunk(0);
        }
    }
    
    function pause() {
        uploadPaused = true;
        
        $("#upload").find(".button_1 i").removeClass("fa-play").addClass("fa-pause");
        $("#upload").find(".button_1 span").text(window.text.uploadButtonC);
    }
    
    function resume() {
        uploadPaused = false;
        
        $("#upload").find(".button_1 i").removeClass("fa-pause").addClass("fa-play");
        $("#upload").find(".button_1 span").text(window.text.uploadButtonB);
        
        sendChunk(chunkPause);
    }
    
    function abort() {
        uploadAborted = true;
        
        var xhr = new XMLHttpRequest();
        xhr.open("post", window.url.cpProfileUpload + "?action=abort&tmp=" + tmp, true);
        xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");

        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4) {
                var jsonParse = JSON.parse(xhr.response);
                
                if(xhr.status !== 200)
                    return;
                
                resetValue("hide");
                
                if (jsonParse.response.upload.processFile !== null)
                    message(true, jsonParse.response.upload.processFile.text);
            }
        };
        
        xhr.send("");
    }
    
    function progress(start) {
        utility.progressBar(start, chunkCurrent);
        
        if (start % 5 === 0) {
            totalTime += (new Date().getTime() - timeStart);
            
            timeLeft = Math.ceil((totalTime / start) * (chunkCurrent - start) / 100);
            
            message(true, timeLeft + window.text.uploadTextC);
        }
    }
    
    function sendChunk(chunk) {
        timeStart = new Date().getTime();
        
        if (uploadAborted === true)
            return;
        
        if (uploadPaused === true) {
            chunkPause = chunk;
            
            message(true, window.text.uploadTextA);
            
            return;
        }
        
        var start = chunk * chunkSize;
        var stop = start + chunkSize;
        
        var reader = new FileReader();
        
        var blob = file.slice(start, stop);
        
        if (navigator.userAgent.indexOf("MSIE") !== -1)
            reader.readAsArrayBuffer(blob);
        else
            reader.readAsBinaryString(blob);

        reader.onloadend = function() {
            var xhr = new XMLHttpRequest();
            xhr.open("post", window.url.cpProfileUpload + "?action=start&tmp=" + tmp, true);
            xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");

            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4) {
                    var jsonParse = JSON.parse(xhr.response);
                    
                    if (xhr.status !== 200)
                        return;
                    
                    if (jsonParse.response.upload.processFile.status === 0) {
                        if (chunk === 0 || tmp === 0)
                            tmp = jsonParse.response.upload.processFile.tmp;

                        if (chunk < chunkCurrent) {
                            progress(chunk + 1);
                            
                            sendChunk(chunk + 1);
                        }
                    }
                    else if (jsonParse.response.upload.processFile.status === 1) {
                        message(true, jsonParse.response.upload.processFile.text);
                        
                        return;
                    }
                    else if (jsonParse.response.upload.processFile.status === 2)
                        sendComplete();
                }
            };
            
            xhr.send(blob);
        };
    }
    
    function sendComplete() {
        var xhr = new XMLHttpRequest();
        xhr.open("post", window.url.cpProfileUpload + "?action=finish&tmp=" + tmp + "&name=" + file.name, true);
        xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");

        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4) {
                var jsonParse = JSON.parse(xhr.response);

                if (xhr.status !== 200)
                    return;
                
                resetValue("hide");
                
                if (jsonParse.response.upload.processFile !== null)
                    message(true, jsonParse.response.upload.processFile.text);
            }
        };
        
        xhr.send("");
    }
    
    function message(show, text) {
        if (show === true)
            $("#upload").find(".container_box").show();
        else
            $("#upload").find(".container_box").hide();
        
        $("#upload").find(".text p").text(text);
    }
    
    function resetValue(type) {
        utility.progressBar();
        
        message(false, "");
        
        $("#upload").find(".button_1 i").removeClass("fa-pause").addClass("fa-play");
        $("#upload").find(".button_1 span").text(window.text.uploadButtonA);
        
        if (type === "show")
            $("#upload").find(".container_controls").show();
        else if (type === "hide") {
            $("#upload").find(".file").val("");
            
            $("#upload").find(".container_controls").hide();
            
            file = null;
        }
        
        tmp = 0;
        uploadStarted = false;
        uploadPaused = false;
        uploadAborted = false;
        chunkCurrent = 0;
        chunkPause = 0;
        timeStart = 0;
        totalTime = 0;
        timeLeft = 0;
    }
}