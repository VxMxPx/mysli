<?php
namespace mysli\web\ui\tplp\menu;
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
    <div class="container default spaced <?php echo $get_alt; ?>" style="min-height:400px;">
        <div class="popup menu point up" style="width: 250px;" >
            <div class="pointer"></div>
            <div class="field search item">
                <input type="text" placeholder="Find..." />
                <button class="action clear" type="button"></button>
            </div>
            <hr class="item"/>
            <a class="item" href="#"><i class="fa fa-cog s-right"></i>Options</a>
            <a class="item" href="#"><i class="fa fa-eye s-right"></i>Preview</a>
            <hr class="item"/>
            <a class="item" href="#">Close</a>
        </div>
        <div class="popup menu point down"  style="width: 250px;left:350px;">
            <div class="pointer"></div>
            <div class="field search item">
                <input type="text" placeholder="Find..." />
                <button class="action go" type="button"></button>
            </div>
            <hr class="item"/>
            <a class="item" href="#"><i class="fa fa-cog s-right"></i>Options</a>
            <a class="item" href="#"><i class="fa fa-eye s-right"></i>Preview</a>
            <hr class="item"/>
            <a class="item" href="#">Close</a>
        </div>
        <div class="popup menu point left" style="top: 300px; width: 100px;" >
            <div class="pointer"></div>
            <a class="item" href="#">Close</a>
        </div>
        <div class="popup menu point right" style="top: 300px; left: 350px; width: 100px;" >
            <div class="pointer"></div>
            <a class="item" href="#">Close</a>
        </div>
    </div>
    <div class="container default spaced <?php echo $get_alt_invert; ?>" style="min-height:400px;">
        <div class="popup menu point up alt" style="width: 250px;" >
            <div class="pointer"></div>
            <div class="field search item alt">
                <input type="text" placeholder="Find..." />
                <button class="action clear" type="button"></button>
            </div>
            <hr class="item"/>
            <a class="item" href="#"><i class="fa fa-cog s-right"></i>Options</a>
            <a class="item" href="#"><i class="fa fa-eye s-right"></i>Preview</a>
            <hr class="item"/>
            <a class="item" href="#">Close</a>
        </div>
        <div class="popup menu point down alt"  style="width: 250px;left:350px;">
            <div class="pointer"></div>
            <div class="field search item alt">
                <input type="text" placeholder="Find..." />
                <button class="action go" type="button"></button>
            </div>
            <hr class="item"/>
            <a class="item" href="#"><i class="fa fa-cog s-right"></i>Options</a>
            <a class="item" href="#"><i class="fa fa-eye s-right"></i>Preview</a>
            <hr class="item"/>
            <a class="item" href="#">Close</a>
        </div>
        <div class="popup menu point left alt" style="top: 300px; width: 100px;" >
            <div class="pointer"></div>
            <a class="item" href="#">Close</a>
        </div>
        <div class="popup menu point right alt" style="top: 300px; left: 350px; width: 100px;" >
            <div class="pointer"></div>
            <a class="item" href="#">Close</a>
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