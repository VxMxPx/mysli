<?php
namespace m;
use mysli\web\assets\tplp\util as assets;
use mysli\js\jquery\tplp\util as jquery;
?><!DOCTYPE html>
<html>
<head>
    <title><?php echo ucfirst($page); ?> :: Mysli Web Ui :: Developer</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=0" />
    <?php echo assets::tags('mysli.js.ui/css/ui.css'); ?>

    <style type="text/css">
        body {
            overflow: hidden;
            background-color: #ebebe1;
        }
    </style>
</head>
<body id="mysli-js-ui-developer" class="mysli-ui">
    <?php echo jquery::tag(); ?>
    <?php echo assets::tags('mysli.js.common/js/common.js'); ?>
    <?php echo assets::tags('mysli.js.ui/js/ui.js'); ?>
    <script>
        <?php echo $script; ?>
    </script>
</body>
</html>