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

});
