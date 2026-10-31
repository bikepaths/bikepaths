<?php
function render_status_card($pageTitle, $heading, $text, $type = 'info', $buttonText = 'Return to Blog', $buttonUrl = '/blog') {
    $iconMap = [
        'success' => '&#10003;',
        'info' => '&#9993;',
        'warning' => '&#33;'
    ];
    $icon = isset($iconMap[$type]) ? $iconMap[$type] : '&#9993;';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?> - BikePaths</title>
    <link rel="stylesheet" href="/blog/themes/blog/css/bootstrap.min.css">
    <link href="//fonts.googleapis.com/css?family=Montserrat:400,700|Lato:300,400" rel="stylesheet">
    <style>
        body {
            background-color: #f7f9fa;
            font-family: 'Lato', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            color: #333333;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px 15px;
            margin: 0;
        }
        .status-container {
            max-width: 480px;
            width: 100%;
        }
        .status-card {
            background: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            padding: 40px 30px;
            text-align: center;
        }
        .status-logo {
            max-height: 44px;
            margin-bottom: 24px;
        }
        .status-icon {
            width: 58px;
            height: 58px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            margin: 0 auto 20px auto;
        }
        .status-icon.success { background: #e8f5e9; color: #2e7d32; }
        .status-icon.info { background: #e3f2fd; color: #1565c0; }
        .status-icon.warning { background: #fff8e1; color: #f57f17; }
        .status-heading {
            font-family: 'Montserrat', sans-serif;
            font-size: 22px;
            font-weight: 700;
            color: #2c3e50;
            margin: 0 0 16px 0;
        }
        .status-text {
            font-size: 15px;
            line-height: 1.6;
            color: #555555;
            margin-bottom: 28px;
        }
        .btn-continue {
            background-color: #2c3e50;
            color: #ffffff !important;
            font-family: 'Montserrat', sans-serif;
            font-size: 14px;
            font-weight: 600;
            padding: 12px 30px;
            border-radius: 4px;
            text-decoration: none;
            display: inline-block;
            transition: background 0.2s ease-in-out;
        }
        .btn-continue:hover, .btn-continue:focus {
            background-color: #1a252f;
            color: #ffffff !important;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="status-container">
        <div class="status-card">
            <a href="/blog"><img src="/blog/themes/blog/images/logo.png" alt="BikePaths" class="status-logo"></a>
            <div class="status-icon <?php echo htmlspecialchars($type); ?>">
                <?php echo $icon; ?>
            </div>
            <h1 class="status-heading"><?php echo htmlspecialchars($heading); ?></h1>
            <div class="status-text"><?php echo $text; ?></div>
            <div>
                <a href="<?php echo htmlspecialchars($buttonUrl); ?>" class="btn-continue"><?php echo htmlspecialchars($buttonText); ?></a>
            </div>
        </div>
    </div>
</body>
</html>
<?php
    exit;
}
?>
