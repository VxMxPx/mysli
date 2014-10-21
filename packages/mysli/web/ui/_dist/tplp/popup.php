<?php
namespace mysli\web\ui\tplp\popup;
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
    <div class="container default spaced <?php echo $get_alt; ?>" style="min-height:250px;">
        <div class="popup point up spaced">
            <div class="pointer"></div>
            <p>Hello World! :)</p>
        </div>
        <div class="popup point down spaced"  style="left:350px;">
            <div class="pointer"></div>
            <p>Hello world! :)</p>
        </div>
        <div class="popup point left spaced" style="top:150px;" >
            <div class="pointer"></div>
            <p>Hello World! :)</p>
        </div>
        <div class="popup point right spaced" style="top:150px;left: 350px;" >
            <div class="pointer"></div>
            <p>Hello World! :)</p>
        </div>
    </div>
    <div class="container default spaced <?php echo $get_alt_invert; ?>" style="min-height:250px;">
        <div class="popup point up alt spaced">
            <div class="pointer"></div>
            <p>Hello World! :)</p>
        </div>
        <div class="popup point down alt spaced" style="left:350px;">
            <div class="pointer"></div>
            <p>Hello World! :)</p>
        </div>
        <div class="popup point left alt spaced" style="top:150px;" >
            <div class="pointer"></div>
            <p>Hello World! :)</p>
        </div>
        <div class="popup point right alt spaced" style="top:150px;left: 350px;" >
            <div class="pointer"></div>
            <p>Hello World! :)</p>
        </div>
    </div>
    <div class="container default spaced <?php echo $get_alt; ?>" style="min-height:120px;">
        <button id="popup-button-click">Click Me!</button>
        <button id="popup-button-hover">Hover!</button>
    </div>
    <?php echo $alt_link; ?>
</div>

<script>
function init() {
    new Mysli.UI.Popup({
        content   : "You've clicked me!",
        trigger   : $('#popup-button-click'),
        event     : 'click',
        selfClose : false
    });
    new Mysli.UI.Popup({
        content   : "You've hovered over me!",
        trigger   : $('#popup-button-hover'),
        position  : 'right',
        event     : 'mouseover',
        delay     : 600,
        selfClose : false
    });
}
</script>
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