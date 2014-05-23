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
        if (num > 6) { clearInterval(panint); }
        panels.add({
            flippable : true,
            front     : {
                title : "Panel: " + num
            },
            back      : {
                title : "Panel Back!"
            }
        }).front.header_add('menu', {
            icon     : 'gear',
            type     : 'link',
            action   : 'click:header/menu',
            position : 'right'
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
    // $('.panel.multi button')
    //     .on('click', function () {
    //         $(this)
    //             .parents('.multi')
    //             .toggleClass('flipped');
    //     });
}, 1000);
</script>
