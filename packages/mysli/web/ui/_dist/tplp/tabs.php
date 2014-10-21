<?php
namespace mysli\web\ui\tplp\tabs;
use mysli\web\assets\tplp\util as assets;
?><!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <title>Mysli UI | <?php echo $title; ?></title>
    <?php echo assets::tags('mysli/web/ui/css/mysli.ui.css'); ?>

    <style type="text/css">
        h1 {
            text-align: center;
        }
        .section {
            max-width: 960px;
            margin: 20px auto 0 auto;
            padding: 10px;
        }
        .section .heading {
            text-align: center;
        }
        #navigation {
            font-size: 12px;
            padding: 2px;
            border-bottom: 1px solid #ccc;
        }
        #navigation a, #navigation strong {
            margin: 0 4px;
        }
        hr {
            border: none;
            border-bottom: 1px dotted #ccc;
        }
        .container {
            position: relative;
            width: 100%;
            box-shadow: 0 0 4px rgba(0, 0, 0, .2);
            overflow: hidden;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .colorful .column {
            background-color: #255277;
            border: 1px solid #173759;
            padding: 10px;
            color: #fe4;
            text-align: center;
            font-size: 16px;
            text-shadow: 0 0 8px rgba(255, 220, 40, .8);
            box-shadow: inset 0 0 4px #3C709A;
        }
        .box.costume {
            width: 400px;
            height: 200px;
            margin: 100px auto 0 auto;
            text-align: center;
        }
    </style>
</head>
<body>
    <div id="navigation">
        <strong>Mysli UI:</strong>
        <?php echo $tplp_func_get_navigation(); ?>
    </div>
<div class="section">
    <div class="container default <?php echo $get_alt; ?>" style="height:400px">
    <div class="panel <?php echo $get_alt; ?>" style="width:50%;min-height:400px;">
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
    <div class="container default <?php echo $get_alt; ?>" style="height:500px">
    <div class="panel <?php echo $get_alt; ?>" style="width:50%;min-height:400px;">
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
            <form class="<?php echo $get_alt; ?>">
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
                    <label for="gender_unknown">I\'d rather not say...</label>
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
    <?php echo $alt_link; ?>
</div>
    <?php echo assets::tags('mysli/web/zepto/zepto.js'); ?>
    <?php echo assets::tags('mysli/web/ui/js/mysli.ui.js'); ?>

    <script>
        if (typeof init === 'function') {
            var ready = setInterval(function () {
                if (typeof $ === 'undefined' || typeof Mysli === 'undefined') { return; }
                clearInterval(ready);
                init();
            }, 1000);
        }
    </script>
</body>
</html>