<!-- <div class="section">
    <div class="container default <?php echo get_alt(); ?>" style="height:600px;border-radius:0;">
    </div>
    <?php echo alt_link(); ?>
</div> -->
<script>
// var resizeTimeout = false;
// window.onresize = function () {
//     if (resizeTimeout) clearTimeout(resizeTimeout);
//     resizeTimeout = setTimeout(function () {
//         $(document).trigger('MU/panels/refresh', [true]);
//     }, 1000);
// };

function init() {
    $('body')
        .css('overflow', 'hidden')
        .html('');

    var panels = new MU.Panels('body'),
        num    = 1;
        // sizes  = ['tiny', 'small', 'medium', 'big'];

    // special panel :)
    var navigation = panels.add({
        flippable : false,
        closable  : false,
        front     : {
            title : "Marko Gajst",
            style : "alt"
        }
    });

    var panint = setInterval(function () {
        if (num > 6) { clearInterval(panint); }
        panels.add({
            // size      : sizes[ ~~(Math.random() * sizes.length) ],
            flippable : true,
            front     : {
                title : "Panel: " + num
            },
            back      : {
                title : "Panel Back!"
            }
        }).front.headerAppend({
            icon     : 'gear',
            type     : 'link',
            action   : 'click:header/menu'
        });
        num = num + 1;
    }, 300);

    $(document).on('click:header/menu', 'div.panel.multi', function () {
        panels.get(this.id).flip();
    });
    $(document).on('self/flip', 'div.panel.multi', function () {
        panels.get(this.id).flip();
    });
    window.panels = panels;
}

var ready = setInterval(function () {
    if (typeof $ === 'undefined' || typeof MU === 'undefined') { return; }
    clearInterval(ready);
    init();
}, 1000);
</script>
