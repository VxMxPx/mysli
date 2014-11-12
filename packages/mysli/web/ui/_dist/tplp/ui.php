<?php
namespace mysli\web\ui\tplp\ui;
use mysli\web\assets\tplp\util as assets;
?><!DOCTYPE html>
<html>
<head>
    <title><?php echo ucfirst($page); ?> :: Mysli Web Ui :: Developer</title>
    <?php echo assets::tags('mysli/web/ui/css-min/ui.css'); ?>

    <style type="text/css">
        nav#primary {
            font-size: 12px;
            padding: 5px;
            background-color: #eee;
            border-bottom: #ddd;
            color: #333;
        }
        nav#primary a {
            color: #336;
            padding: 5px;
            text-decoration: none;
        }
        nav#primary a.selected {
            background-color: #fff;
            font-weight: bold;
        }
    </style>
</head>
<body id="mysli-web-ui-developer">
    <nav id="primary">
        <a class="<?php echo ($page == 'index') ? 'selected' : 'defalt'; ?>" href="?script=index">Home</a>
        <a class="<?php echo ($page == 'buttons') ? 'selected' : 'defalt'; ?>" href="?script=buttons">Buttons</a>
    </nav>
    <div class="container">
    </div>
    <?php echo assets::tags('mysli/external/zepto/zepto.js'); ?>
    <?php echo assets::tags('mysli/web/ui/ui.js'); ?>
    <script>
        <?php echo $script; ?>
    </script>
</body>
</html>