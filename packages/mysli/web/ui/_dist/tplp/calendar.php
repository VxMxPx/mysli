<?php
namespace mysli\web\ui\tplp\calendar;
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
    <div class="container default spaced" style="min-height:400px;">

    <?php mk_calendar($get_alt, 20); ?>
    <?php mk_calendar($get_alt, 350, true); ?>
    <?php function mk_calendar($get_alt, $position, $disabled = false) { ?>
            <div class="popup point up <?php echo $get_alt; ?>" style="left:<?php echo $position; ?>px;">
                <div class="pointer"></div>
                <table class="calendar data <?php echo $get_alt; ?>">
                    <caption>
                        <a href="#" class="left">&lt;</a>
                        <span>April 2014</span>
                        <a href="#" class="right">&gt;</a>
                    </caption>
                    <thead>
                        <tr>
                            <th>M</th>
                            <th>T</th>
                            <th>W</th>
                            <th>T</th>
                            <th>F</th>
                            <th>S</th>
                            <th>S</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="<?php echo $disabled ? 'disabled' : ''; ?> fade">31</td>
                            <td class="<?php echo $disabled ? 'disabled' : ''; ?>">1</td>
                            <td class="<?php echo $disabled ? 'disabled' : ''; ?>">2</td>
                            <td class="<?php echo $disabled ? 'disabled' : ''; ?>">3</td>
                            <td class="<?php echo $disabled ? 'disabled' : ''; ?>">4</td>
                            <td class="<?php echo $disabled ? 'disabled' : ''; ?>">5</td>
                            <td class="<?php echo $disabled ? 'disabled' : ''; ?>">6</td>
                        </tr>
                        <tr>
                            <td class="<?php echo $disabled ? 'disabled' : ''; ?>">7</td>
                            <td class="<?php echo $disabled ? 'disabled' : ''; ?>">8</td>
                            <td class="<?php echo $disabled ? 'disabled' : ''; ?>">9</td>
                            <td class="<?php echo $disabled ? 'disabled' : ''; ?>">10</td>
                            <td>11</td>
                            <td>12</td>
                            <td>13</td>
                        </tr>
                        <tr>
                            <td>14</td>
                            <td>15</td>
                            <td class="selected">16</td>
                            <td>17</td>
                            <td>18</td>
                            <td>19</td>
                            <td>20</td>
                        </tr>
                        <tr>
                            <td>21</td>
                            <td>22</td>
                            <td>23</td>
                            <td>24</td>
                            <td>25</td>
                            <td class="today">26</td>
                            <td>27</td>
                        </tr>
                        <tr>
                            <td>28</td>
                            <td>29</td>
                            <td>30</td>
                            <td class="fade">1</td>
                            <td class="fade">2</td>
                            <td class="fade">3</td>
                            <td class="fade">4</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        <?php } ?>
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