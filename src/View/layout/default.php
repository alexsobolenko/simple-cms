<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title><?= $this->getTitle() ?></title>
    <link rel="stylesheet" href="/assets/css/bootstrap.min.css">
    <?= $this->content('stylesheet') ?>
</head>
<body>
    <div class="container">
        <?= $this->content('content') ?>
    </div>
    <script src="/assets/js/bootstrap.min.js"></script>
    <?= $this->content('javascript') ?>
</body>
</html>
