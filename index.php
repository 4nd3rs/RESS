<?php include "header.php" ?>

<div class="container">
    <div class="row">
        <!--<div class="<?php /*echo $RESS["width"] >= 768 ? 'span8' :  'span12'*/?>">-->
        <div class="span8">
            <?php include "fragments/carousel.php" ?>
            <?php include "fragments/detection/device.php" ?>
            <?php include "fragments/detection/feature.php" ?>
            <?php include "fragments/detection/ress.php" ?>
        </div>

        <div class="span4">
            <?php include "fragments/archive.php"?>
            <?php if ($RESS["width"] >= 768) { ?>
                <div class="max-768">
                    <h2>Social</h2>
                    <?php include "fragments/twitter-search.php"?>
                    <?php include "fragments/facebook.php"?>
                </div>
            <?php}?>
        </div>

    </div>
</div><!-- /container -->

<?php include "footer.php" ?>

