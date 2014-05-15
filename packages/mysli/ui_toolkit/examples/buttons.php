<div class="section">
    <h2>Button Styles</h2>
    <div class="container default spaced <?php echo get_alt(); ?>">
        <button type="button"><span>Default</span></button>
        <button class="alt" type="button"><span>Inverse</span></button>
        <button class="primary" type="button"><span>Primary</span></button>
        <button class="attention" type="button"><span>Attention</span></button>
        <button disabled="true" type="button"><span>Disabled</span></button>
        <button type="button"><i class="fa fa-heart"></i><span>Icon!</span></button>
        <button class="alt" type="button"><span>Icon!</span><i class="fa fa-heart"></i></button>
        <button class="attention" type="button"><i class="fa fa-heart"></i></button>
    </div>

    <h2>Button Bars</h2>
    <div class="container default spaced <?php echo get_alt(); ?>">
        <div class="group">
            <button type="button"><span>One</span></button>
            <button type="button"><span>Two</span></button>
            <button type="button"><span>Three</span></button>
        </div>
        <div class="group" style="margin-left:10px;float:left;">
            <button class="alt" type="button"><span>One</span></button>
            <button class="alt pressed" type="button"><span>Two</span></button>
            <button class="alt" type="button"><span>Three</span></button>
            <button class="alt" type="button"><span>Four</span></button>
            <button class="alt" type="button"><span>Five</span></button>
            <button class="alt" disabled="true" type="button"><span>Six</span></button>
            <button class="alt" type="button"><span>Seven</span></button>
            <button class="alt" type="button"><span>Eight</span></button>
            <button class="alt" type="button"><span>Nine</span></button>
            <button class="alt" type="button"><span>Ten</span></button>
        </div>
    </div>

    <h2>Button Functions</h2>
    <div class="container default spaced <?php echo get_alt(); ?>">
        <button class="alt disable" type="button"><span>Disable Me</span></button>
        <button class="enable" type="button"><span><i class="fa fa-arrow-left"></i> Enable</span></button>

        <button class="alt bbusy" type="button"><span>Set Me Busy!</span></button>
        <button class="relax" type="button"><span><i class="fa fa-arrow-left"></i> Relax!</span></button>
    </div>

    <?php echo alt_link(); ?>

    <!-- Scripts -->
    <script>
    function buttons_events() {
        var disable = new MU.Button('button.disable');
        var enable  = new MU.Button('button.enable');
        disable.on('click', function () {
            disable.disabled(true);
        });
        enable.on('click', function () {
            disable.disabled(false);
        });

        var busy = new MU.Button('button.bbusy');
        var relax = new MU.Button('button.relax');
        busy.on('click', function () {
            busy.busy(true);
        });
        relax.on('click', function () {
            busy.busy(false);
        });

    }

    var ready = setInterval(function () {
        if (typeof MU === 'undefined') { return; }
        clearInterval(ready);
        buttons_events();
    }, 1000);
    </script>
</div>