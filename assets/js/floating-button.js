jQuery(document).ready(function($) { 
    const $mainButton = $(".main-button"),
          $closeButton = $(".close-button"),
          $buttonWrapper = $(".button-wrapper"),
          $layer = $(".layered-content");

    // console.log("Document ready. Elements found:", $mainButton.length, $closeButton.length, $buttonWrapper.length, $layer.length);

    $mainButton.on("click", function() {
        // console.log("Main button clicked");
        $buttonWrapper.addClass("clicked").delay(500).queue(function(next) {
            // console.log("Modal opening animation triggered");
            $layer.addClass("active");
            next();
        });
    });

    $closeButton.on("click", function() {
        // console.log("Close button clicked");
        $layer.removeClass("active");
        $buttonWrapper.removeClass("clicked");
    });

    $layer.on("click", function(event) {
        // console.log("Clicked inside modal, preventing close");
        event.stopPropagation();
    });
});
