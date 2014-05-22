<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <title>Mysli UI Toolkit :: <?php echo get_title(); ?></title>
    <link rel="stylesheet" type="text/css" href="/assets/dist/css/mysli_ui_toolkit.css">
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
        hr {
            border: none;
            border-bottom: 1px dotted #ccc;
        }
        ul.examples {
            list-style: none;
        }
        ul.examples li {
            float: left;
            margin: 5px;
        }
        ul.examples li a {
            border-radius: 4px;
            box-shadow: inset 0 -2px 0 rgba(0, 0, 0, .2);
            background-color: #eee;
            display: block;
            width: 120px;
            height: 120px;
            text-align: center;
            color: #333;
            padding: 40px 0;
            text-decoration: none;
            transition: background-color .4s, color .4s;
        }
        ul.examples li a:hover {
            background-color: #333;
            color: #fff;
        }
        .container {
            position: relative;
            width: 100%;
            box-shadow: 0 0 4px rgba(0, 0, 0, .2);
            overflow: hidden;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .container.alt {
        }
        .colorful .column {
            background-color: #900a0a;
            border: 1px solid #a05959;
            padding: 10px;
            color: #fe4;
            text-align: center;
            font-size: 24px;
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
    <?php
    function url($key, $val) {
        $query = $_GET;
        $query[$key] = $val;
        return $_SERVER['SCRIPT_NAME'] . '?' . http_build_query($query);
    }
    function alt_link() {
        return '<a href="' . url(
            'alt', (isset($_GET['alt']) && $_GET['alt'] === 'true' ? 'false' : 'true')
        ) .
        '">Inverse</a>';
    }
    function get_alt($double = false) {
        if ((isset($_GET['alt']) and $_GET['alt'] === 'true')) {
            $alt = true;
        } else {
            $alt = false;
        }
        $alt = $double ? !$alt : $alt;
        return ($alt ? 'alt' : '');
    }
    function get_title()
    {
        return ( isset($_GET['file']) ? ucwords(str_replace('_', ' ', $_GET['file'])) : 'Examples' );
    }
    ?>

    <div class="section" style="margin-top:0;">
        <h1>
            <a href="<?php echo $_SERVER['SCRIPT_NAME']; ?>">Mysli UI Toolkit</a> &rarr; <?php echo get_title(); ?>
        </h1>
    </div>

    <hr/>

    <?php
    if (!isset($_GET['file'])) {
        $files = scandir(__DIR__);
        echo '<div class="section">',
        '<ul class="examples">';
        foreach ($files as $file)
            substr($file, -4) === '.php' and ($file !== 'index.php') and print(
                '<li><a href="' . url('file', substr($file, 0, -4)) . '">' .
                ucwords(str_replace('_', ' ', substr($file, 0, -4))) . '</a></li>'
            );
        echo '</ul>',
        '</div>';
    } else {
        $file = $_GET['file'];
        $file = preg_replace('/[^a-z0-9_]/i', '', $file);
        $file_full = __DIR__ . '/' . $file . '.php';
        if (file_exists($file_full) && $file !== 'index') {
            include $file_full;
        } else {
            trigger_error("File not found: {$file}", E_USER_ERROR);
        }
    }
    ?>

    <script src="http://zeptojs.com/zepto.js"></script>
    <script src="https://rawgithub.com/madrobby/zepto/master/src/fx.js"></script>
    <script src="https://rawgithub.com/madrobby/zepto/master/src/fx_methods.js"></script>
    <script src="https://rawgithub.com/madrobby/zepto/master/src/selector.js"></script>
    <?php
        $scripts = file_get_contents(__DIR__ . '/../assets.json');
        $scripts = json_decode($scripts, true);
        foreach ($scripts['js/mysli_ui_toolkit.js'] as $script) {
            echo '<script src="/assets/src/'.$script.'"></script>'."\n";
        }
    ?>
</body>
</html>
