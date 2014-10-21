<?php
namespace mysli\web\ui\tplp\table;
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
    <div class="container default <?php echo $get_alt; ?>" style="height:600px">
        <div class="panel <?php echo $get_alt; ?>" style="width:50%;">
        <header>
            <h2>List of Slovene Cities</h2>
        </header>
        <div class="">
        <table class="data sh sv zebra <?php echo $get_alt; ?>">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Population</th>
                    <th>Town Rights</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Ljubljana</td>
                    <td><small>258873</small></td>
                    <td><small>1220 - 1243</small></td>
                </tr>
                <tr>
                    <td>Maribor</td>
                    <td><small>151349</small></td>
                    <td><small>13th century</small></td>
                </tr>
                <tr class="selected">
                    <td>Celje</td>
                    <td><small>37834</small></td>
                    <td><small>1451</small></td>
                </tr>
                <tr class="selected">
                    <td>Kranj</td>
                    <td><small>35587</small></td>
                    <td><small>1256</small></td>
                </tr>
                <tr>
                    <td>Velenje</td>
                    <td><small>26742</small></td>
                    <td><small>1959</small></td>
                </tr>
                <tr>
                    <td>Ptuj</td>
                    <td><small>23957</small></td>
                    <td><small>1376</small></td>
                </tr>
                <tr>
                    <td>Koper</td>
                    <td><small>23726</small></td>
                    <td><small>before 1230</small></td>
                </tr>
                <tr>
                    <td>Novo mesto</td>
                    <td><small>22415</small></td>
                    <td><small>1365</small></td>
                </tr>
                <tr>
                    <td>Trbovlje</td>
                    <td><small>17485</small></td>
                    <td><small>1952</small></td>
                </tr>
                <tr>
                    <td>Nova Gorica</td>
                    <td><small>13491</small></td>
                    <td><small>1948 (1455 Italin Gorica)</small></td>
                </tr>
            </tbody>
        </table>
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