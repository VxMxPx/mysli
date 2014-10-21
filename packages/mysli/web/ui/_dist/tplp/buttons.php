<?php
namespace mysli\web\ui\tplp\buttons;
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
    <h2>Button Styles</h2>
    <div class="container default spaced <?php echo $get_alt; ?>">
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
    <div class="container default spaced <?php echo $get_alt; ?>">
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
    <div class="container default spaced <?php echo $get_alt; ?>">
        <button class="alt disable" type="button"><span>Disable Me</span></button>
        <button class="enable" type="button"><span><i class="fa fa-arrow-left"></i> Enable</span></button>
        <button class="alt bbusy" type="button"><span>Set Me Busy!</span></button>
        <button class="relax" type="button"><span><i class="fa fa-arrow-left"></i> Relax!</span></button>
    </div>
    <?php echo $alt_link; ?>

    <!-- Scripts -->
    <script>
    function init() {
        var MU = Mysli.UI;
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
            busy.busy(true, 'I\'m busy...');
        });
        relax.on('click', function () {
            busy.busy(false);
        });

    }
    </script>
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