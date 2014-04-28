<div class="section">
    <div class="container default spaced <?php echo get_alt(); ?>">
        <button class="first" type="button"><span>Button</span></button>
        <button class="alt" type="button"><span>Inverse</span></button>
        <button class="primary" type="button"><span>Primary</span></button>
        <button class="attention" type="button"><span>Attention</span></button>
        <button disabled="true" type="button"><span>Disabled</span></button>
        <button type="button"><i class="fa fa-heart"></i><span>Icon!</span></button>
        <button class="alt" type="button"><span>Icon!</span><i class="fa fa-heart"></i></button>
        <button class="attention" type="button"><i class="fa fa-heart"></i></button>
    </div>
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
    <?php echo alt_link(); ?>
</div>
