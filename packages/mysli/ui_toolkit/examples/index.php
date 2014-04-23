<!DOCTYPE html>
<html>
<head>
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
            margin: 10px;
        }
        ul.examples li a {
            background-color: #eee;
            box-shadow: 0 0 12px rgba(0, 0, 0, .1);
            border: 1px solid #ddd;
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
            background-color: #222;
            color: #fff;
        }
        .container {
            position: relative;
            width: 100%;
            /*height: 400px;*/
            /*background-color: #eee;*/
            /*outline: 1px solid #ddd;*/
            /*border: 1px solid #ddd;*/
            box-shadow: 0 0 4px rgba(0, 0, 0, .2);
            overflow: hidden;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .colorful .column {
            background-color: #b00a0a;
            border: 1px solid #d07979;
            padding: 10px;
            color: #d07979;
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
    function inverse_link() {
        return '<a href="' . url(
            'inverse', (isset($_GET['inverse']) && $_GET['inverse'] === 'true' ? 'false' : 'true')
        ) .
        '">Inverse</a>';
    }
    function get_inverse($double = false) {
        if ((isset($_GET['inverse']) and $_GET['inverse'] === 'true')) {
            $inverse = true;
        } else {
            $inverse = false;
        }
        $inverse = $double ? !$inverse : $inverse;
        return ($inverse ? 'inverse' : '');
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
    <script src="/assets/dist/js/mysli_ui_toolkit.js"></script>
</body>
</html>
