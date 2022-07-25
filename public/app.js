$(document).ready(function() {

    new ClipboardJS('.btn-clipboard');

    $(".delete").click(function(){
        if (!confirm("Êtes-vous sûr de vouloir supprimer cette image ?")){
            return false;
        }
    });

    tagcloud();
});

tagcloud = function() {
    var biggest = 0;
    var smallest = Infinity;

    var width = $('#tagcloud').width();
    var height = width/1.5;

    var max = width/5;
    var min = max/10;

    var colors = d3.scaleOrdinal(['#111', '#222', '#333', '#444', '#555', '#666']);

    var words = [];
    $('#tagcloud a').map(function(i, word) {
        words[i] = {
            text: $(word).text(),
            size: $(word).data('weight'),
            url: $(word).attr("href")
        };
        biggest = Math.max(biggest, words[i].size);
        smallest = Math.min(smallest, words[i].size);
    });


    d3.layout.cloud().size([width, height])
        .words(words)
        .font("Impact")
        .fontSize(function(word) {
            return (((max-min) * word.size)  - (max*smallest) + (min*biggest)) / (biggest - smallest);
        })
        .on("end", draw)
        .start();

    function draw(words) {
        d3.select("#tagcloud")
        .append("svg").attr("preserveAspectRatio", "xMinYMin meet").attr("viewBox", "0 0 " + width + " " + height)
        .append("g").attr("transform", "translate(" + (width/2) + ',' + (height/2) + ")")
        .selectAll("text").data(words)
        .enter().append("text")
            .style("font-size", function(word) {
                return word.size + "px";
            })
            .style("font-family", "Impact")
            .style("fill", function(word, i) {
                return colors(i);
            })
            .attr("text-anchor", "middle")
            .attr("transform", function(word) {
                return "translate(" + [word.x, word.y] + ")rotate(" + word.rotate + ")";
            })
            .text(function(word) {
                return word.text;
            })
            .on("click", function (e, word, i){
                window.location.href = word.url;
            });
    }
}
