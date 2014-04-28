<div class="section">
    <div class="container default <?php echo get_alt(); ?>" style="height:300px;">
        <div class="panel <?php echo get_alt(); ?>" style="z-index:1;">
            <header>
                <h2>#1</h2>
            </header>
            <div class="loading"></div>
        </div>
        <div class="panel selected <?php echo get_alt(); ?>">
            <header>
                <a class="close left" href="#close"></a>
                <h2>#2</h2>
                <a class="icon menu right" href="#menu"><i class="fa fa-cog"></i></a>
            </header>
        </div>
    </div>
    <div class="container <?php echo get_alt(); ?>" style="height:300px;overflow:visible;">
        <div class="panel <?php echo get_alt(true); ?>" style="z-index:3;">
            <header>
                <a class="left" href="#"><i class="fa fa-arrow-left"></i></a>
                <h2>#1</h2>
            </header>
        </div>
        <div class="panel <?php echo get_alt(); ?>" style="z-index:2;">
            <header>
                <a class="close left" href="#close"></a>
                <h2>#2</h2>
                <a class="icon menu right" href="#menu"><i class="fa fa-cog"></i></a>
            </header>
        </div>
        <div class="panel multi z-index:1;">
            <div class="sides">
                <div class="front side panel">
                    <header>
                        <h2>List</h2>
                    </header>
                    <div class="spaced">
                        <button type="button" style="width:100%;">Flip Me!</button>
                    </div>
                </div>
                <div class="back side panel alt">
                    <header>
                        <h2>Options</h2>
                    </header>
                    <div class="spaced">
                        <button class="alt" type="button" style="width:100%;">Flip Me!</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php echo alt_link(); ?>
</div>
<script>
    var ready = setInterval(function () {
        if (!$) { return; }
        clearInterval(ready);
        $('.panel.multi button')
            .on('click', function () {
                $(this)
                    .parents('.multi')
                    .toggleClass('flipped');
            });

    }, 1000);
</script>
