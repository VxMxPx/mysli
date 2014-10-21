<?php
namespace mysli\web\ui\tplp\form;
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
    <div class="container default <?php echo $get_alt; ?>">
        <form class="<?php echo $get_alt; ?>">
        <div class="row">
            <div class="column spaced x4">
                <div class="field">
                    <label for="ff_name">Name:</label>
                    <input type="text" id="ff_name" />
                </div>
                <div class="field">
                    <label for="ff_password">Password:</label>
                    <input type="password" id="ff_password" />
                </div>
                <div class="field">
                    <label for="ff_about">About:</label>
                    <textarea id="ff_about"></textarea>
                </div>
                <div class="field">
                    <label for="ff_fruit">Favorite Fruit:</label>
                    <select id="ff_fruit">
                        <option>Banana</option>
                        <option>Pear</option>
                        <option>Apple</option>
                        <option>Orange</option>
                        <option>Kiwi</option>
                        <option>Pineapple</option>
                        <option>Mango</option>
                    </select>
                </div>
                <div class="field">
                    <label for="ff_disabled">Disabled:</label>
                    <input type="text" disabled="true" id="ff_disabled" />
                </div>
                <div class="field">
                    <label for="ff_textarea_disabled">Disabled:</label>
                    <textarea id="ff_textarea_disabled" disabled="true"></textarea>
                </div>
                <div class="field">
                    <label for="ff_select_disabled">Disabled:</label>
                    <select id="ff_select_disabled" disabled="true">
                        <option>Banana</option>
                    </select>
                </div>
            </div>
            <div class="column spaced x4">
                <div class="field search">
                    <input type="text" placeholder="Find..." />
                    <button class="action go" type="button"></button>
                </div>
                <div class="field">
                    <label>Gender:</label>
                    <input type="radio" id="ff_gender_male" name="gender" />
                    <label for="ff_gender_male">Male</label>
                    <input type="radio" id="ff_gender_female" name="gender" />
                    <label for="ff_gender_female">Female</label>
                    <input type="radio" id="ff_gender_not" name="gender" />
                    <label for="ff_gender_not">I'd rather not say...</label>
                    <input type="radio" id="ff_gender_na" name="gender" disabled="true" />
                    <label for="ff_gender_na">Not available</label>
                </div>
                <div class="field">
                    <label>Your Pets:</label>
                    <input type="checkbox" id="ff_pet_cat" name="pet" />
                    <label for="ff_pet_cat">Cat</label>
                    <input type="checkbox" id="ff_pet_dog" name="pet" checked="true" />
                    <label for="ff_pet_dog">Dog</label>
                    <input type="checkbox" id="ff_pet_goat" name="pet" checked="true" />
                    <label for="ff_pet_goat">Goat</label>
                    <input type="checkbox" id="ff_pet_piglet" name="pet" />
                    <label for="ff_pet_piglet">Piglet</label>
                    <input type="checkbox" id="ff_pet_chicken" name="pet" />
                    <label for="ff_pet_chicken">Chicken</label>
                    <input type="checkbox" id="ff_pet_na" name="pet" disabled="true" />
                    <label for="ff_pet_na">Not available</label>
                </div>
                <div class="field">
                    <button type="button">Save</button>
                </div>
            </div>
        </div>
        </form>
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