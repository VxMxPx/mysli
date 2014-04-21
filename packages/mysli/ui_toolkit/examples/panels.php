<div class="section">
    <div class="container default <?php echo get_inverse(); ?>" style="height:300px;">
        <div class="panel <?php echo get_inverse(); ?>" style="z-index:1;">
            <header>
                <h2>#1</h2>
            </header>
        </div>
        <div class="panel <?php echo get_inverse(); ?>">
            <header>
                <a class="close left" href="#close"></a>
                <h2>#2</h2>
                <a class="icon menu right" href="#menu"><i class="fa fa-cog"></i></a>
            </header>
        </div>
    </div>
    <div class="container <?php echo get_inverse(); ?>" style="height:300px;">
        <div class="panel <?php echo get_inverse(true); ?>" style="z-index:1;">
            <header>
                <h2>#1</h2>
            </header>
        </div>
        <div class="panel <?php echo get_inverse(); ?>">
            <header>
                <a class="close left" href="#close"></a>
                <h2>#2</h2>
                <a class="icon menu right" href="#menu"><i class="fa fa-cog"></i></a>
            </header>
        </div>
    </div>
    <?php echo inverse_link(); ?>
</div>
