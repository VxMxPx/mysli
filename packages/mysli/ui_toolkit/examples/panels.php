<div class="section">
    <div class="container default <?php echo get_alt(); ?>" style="height:600px;border-radius:0;">
    </div>
    <?php echo alt_link(); ?>
</div>
<script>
function init() {
    var panels = new MU.Panels('div.container'),
        num    = 1;

    var panint = setInterval(function () {
        if (num > 4) { clearInterval(panint); }
        panels.add({
            front     : {
                title : "Panel: " + num
            }
        });
        num = num + 1;
    }, 500);
}

var ready = setInterval(function () {
    if (typeof $ === 'undefined' || typeof MU === 'undefined') { return; }
    clearInterval(ready);
    init();
    // $('.panel.multi button')
    //     .on('click', function () {
    //         $(this)
    //             .parents('.multi')
    //             .toggleClass('flipped');
    //     });
}, 1000);
</script>
