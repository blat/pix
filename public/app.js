$(document).ready(function() {

    ZeroClipboard.setDefaults({
        moviePath: '//cdnjs.cloudflare.com/ajax/libs/zeroclipboard/1.1.7/ZeroClipboard.swf',
        trustedDomains: ['*'],
        hoverClass: "hover",
        activeClass: "hover"
    });

    new ZeroClipboard($('[rel=zero-clipboard]'));

    $(".delete").click(function(){
        if (!confirm("Êtes-vous sûr de vouloir supprimer cette image ?")){
            return false;
        }
    });

    $("#tagcloud").awesomeCloud({
        "size" : {
            "grid" : 8, // word spacing, smaller is more tightly packed
            "factor" : 0, // font resize factor, 0 means automatic
            "normalize" : false // reduces outliers for more attractive output
        },
        "color" : {
            "background" : "rgba(255,255,255,0)", // background color, transparent by default
            "start" : "#666", // color of the smallest font, if options.color = "gradient""
            "end" : "#111" // color of the largest font, if options.color = "gradient"
        },
        "options" : {
            "color" : "gradient", // if "random-light" or "random-dark", color.start and color.end are ignored
            "rotationRatio" : 0.2, // 0 is all horizontal, 1 is all vertical
            "printMultiplier" : 1, // set to 3 for nice printer output; higher numbers take longer
            "sort" : "highest" // "highest" to show big words first, "lowest" to do small words first, "random" to not care
        },
        "font" : "Helvetica, Arial, sans-serif", // the CSS font-family string
        "shape" : "circle" // the selected shape keyword, or a theta function describing a shape
    });

});
