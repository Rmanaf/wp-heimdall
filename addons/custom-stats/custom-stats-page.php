<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo __("Custom Stats" , "heimdall");?></title>
</head>

<body>
    <?php
    $code = get_option('wp_dcp_heimdall_custom_stats', '');
    echo do_shortcode($code);
    ?>
</body>

</html>