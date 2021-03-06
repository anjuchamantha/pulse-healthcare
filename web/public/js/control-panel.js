$("#drawer-open").click(function (e) {
    e.preventDefault();
    $(".sidebar-fixed").animate({'marginLeft': '0'}, 200);
});


$("#drawer-close").click(function (e) {
    e.preventDefault();
    $(".sidebar-fixed").animate({'marginLeft': '-270px'}, 200);
});

$(".sidebar-button").click(
    function () {
        let buttons = $(".sidebar-button");
        let iframe = $("#content-iframe");

        let newLoc = window.location.origin + window.location.pathname + '/' + $(this).attr('id');

        if (iframe.attr("src") !== newLoc) {
            buttons.removeClass("active");
            buttons.removeClass("rounded");
            buttons.removeClass("amber");
            buttons.removeClass("light");
            buttons.addClass("light");

            $(this).toggleClass("active");
            $(this).toggleClass("rounded");
            $(this).toggleClass("amber");
            $(this).toggleClass("light");

            iframe.fadeOut(200,function(){
                iframe.attr('src', newLoc );
                setTimeout(function() {
                    iframe.fadeIn(200);
                }, 200);
            });
        }
    }
);

// Wallpaper dynamic set
$(document).ready(function () {
    // let pattern = Trianglify({
    //     width: 2000,
    //     height: 2000,
    //     cell_size: 50,
    //     x_colors: ['#D84315', '#1565C0', '#000000']
    // });
    //
    // let dataUrl = pattern.canvas().toDataURL();
    // $(".sidebar-fixed").css("background-image", 'url(' + dataUrl + ')');
});
