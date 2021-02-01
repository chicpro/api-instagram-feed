<?php
ini_set('opcache.enable', '0');

require __DIR__ . '/common.php';

try {
    $result = $insta->getMedia();

    $result = json_decode($result);

    $list = $result->data;
} catch (Exception $e) {
    die($e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="ko">

<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes" />
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width,minimum-scale=1.0" />

<title>Instagram Images</title>

<style>
    .my_instagram {
        list-style: none;
        padding: 0;
        margin: 0;
        display: block;
    }

    .my_instagram li {
        float: left;
        width: 20%;
    }

    .my_instagram li img {
        max-width: 100%;
    }

    .my_instagram li a {
        display: block;
    }
</style>
</head>

<body>

    <ul class="my_instagram">
        <?php
        foreach ($list as $row) {
            ?>
        <li>
            <a href="<?php echo $row->permalink; ?>" target="_blank"><img src="<?php echo $row->media_url; ?>"><?php echo $row->caption; ?></a>
        </li>
        <?php
        }
        ?>
    <ul>

</body>
</html>