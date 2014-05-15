<div class="section">
    <div class="container default <?php echo get_alt(); ?>">
    <div class="panel <?php echo get_alt(); ?>" style="width:50%;min-height:400px;">
        <header>
            <a href="#close" class="left close"></a>
            <h2>User Profile</h2>
        </header>
        <div class="tabs center">
            <a href="#" class="tab">Tab One</a>
            <a href="#" class="tab">Tab Two</a>
            <a href="#" class="tab active">Tab Three</a>
        </div>
    </div>
    </div>

    <div class="container default <?php echo get_alt(); ?>">
    <div class="panel <?php echo get_alt(); ?>" style="width:50%;min-height:400px;">
        <header>
            <a href="#close" class="left close"></a>
            <h2>User Profile</h2>
        </header>
        <div class="tabs">
            <a href="#" class="tab"><i class="fa fa-sign-in s-right"></i>Login</a>
            <a href="#" class="tab active"><i class="fa fa-user s-right"></i>Personal</a>
            <a href="#" class="tab"><i class="fa fa-lock s-right"></i>Permissions</a>
        </div>
        <div style="padding:20px;">
            <form class="<?php echo get_alt(); ?>">
                <div class="field">
                    <label for="full_name">Your Full Name:</label>
                    <input type="text" id="full_name" placeholder="e.g. John Doe" />
                </div>
                <div class="field">
                    <label for="birth_date">Date of Birth:</label>
                    <input type="text" id="birth_date" placeholder="24/2/1990" />
                </div>
                <div class="field">
                    <label>Gender:</label>
                    <input type="radio" name="gender" id="gender_male" />
                    <label for="gender_male">Male</label>
                    <input type="radio" name="gender" id="gender_female" />
                    <label for="gender_female">Female</label>
                    <input type="radio" name="gender" id="gender_unknown" />
                    <label for="gender_unknown">I'd rather not say...</label>
                </div>
                <div class="field divided top row">
                    <div class="column x4">
                        <button class="primary" type="button">Save!</button>
                    </div>
                    <div class="column x4 to-right">
                        <button type="button">Cancel</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    </div>
    <?php echo alt_link(); ?>
</div>