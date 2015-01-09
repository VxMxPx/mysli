<?php
namespace mysli\web\ui\tplp\ui;
use mysli\web\assets\tplp\util as assets;
use mysli\external\jquery\tplp\util as jquery;
?><!DOCTYPE html>
<html>
<head>
    <title><?php echo ucfirst($page); ?> :: Mysli Web Ui :: Developer</title>
    <?php echo assets::tags('mysli/web/ui/css-min/ui.css'); ?>

    <style type="text/css">
        body {
            overflow: hidden;
        }
        .ui-panel-container {
            background-color: #3a2b2b;
        }
        .ui-panel {
            box-shadow: 2px 0px 6px rgba(0, 0, 0, .2);
        }
    </style>
</head>
<body id="mysli-web-ui-developer" class="mysli-ui">
    <?php echo jquery::tag(); ?>
    <?php echo assets::tags('mysli/web/ui/ui.js'); ?>
    <script>
        <?php echo $script; ?>
    </script>
</body>
</html>