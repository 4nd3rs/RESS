<!--Load capabilities into an global JS variable-->
<script type="text/javascript">

    RESS = {};


    //Util function
    RESS.readCookie = function (name) {
        var nameEQ = name + "=";
        var ca = document.cookie.split(';');
        for (var i = 0; i < ca.length; i++) {
            var c = ca[i];
            while (c.charAt(0) == ' ') c = c.substring(1, c.length);
            if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length, c.length);
        }
        return null;
    }

    RESS.writeCookie = function (name, value) {
        var date = new Date()
        date.setFullYear(date.getFullYear() + 1);
        document.cookie = name + '=' + value + '; expires=' + date.toUTCString() + '; path=/;';
    }

    //Store stuff in cookies
    RESS.storeSizes = function () {
        //Get screen width
        var width = window.innerWidth;

        // Set a cookie with the client side capabilities.
        RESS.writeCookie("RESS", "width." + width);
        var widthElem = document.getElementById("width");
        if(widthElem){
            widthElem.innerHTML = window.innerWidth;
        }


    }

    RESS.storeSizes();

    RESS.isResizeActive = false;

    //register resize event
    window.onresize = function (event) {
        if (!RESS.isResizeActive) {
            RESS.isResizeActive = true;

            //make sure we do not do this too often...
            window.setTimeout(function () {
                RESS.storeSizes();

                RESS.isResizeActive = false;
            }, 1000);
        }
    }
</script>

<?php
$RESSCookie = $_COOKIE['RESS'];
if ($RESSCookie) {
    $RESSValues = explode('|', $RESSCookie);
    $featureCapabilities;
    foreach ($RESSValues as $RESSValue) {
        $capability = explode('.', $RESSValue);
        $featureCapabilities[$capability[0]] = $capability[1];
    }
}

$WURFLWidth = $client->getDeviceCapability('max_image_width');
if ($client->getDeviceCapability('ux_full_desktop')) {
    $WURFLWidth = 1440;
}

//set capas, try to get them from cookie first
$defaultWidth = ($featureCapabilities["width"] ? $featureCapabilities["width"] : $WURFLWidth);

//select correct image version
if ($defaultWidth < 320) {
    //small screens get 240 image
    $imageVersion = "320";
} else if ($defaultWidth < 500) {
    //320-480 screens get 500
    $imageVersion = "500";
} else if ($defaultWidth <= 1024) {
    //screens between 500 and 640 get 640
    $imageVersion = "640";
} else {
    //anything larger than 640 get 1024
    $imageVersion = "770";
}

global $RESS;
$RESS = array(
    "width" => $defaultWidth,
    "imageVersion" => $imageVersion);
?>
<script type="text/javascript">
    var RESS_Capas = {
        'RESS_WIDTH':<?php echo $RESS["width"] ?>,
        'RESS_IMAGEVERSION':<?php echo $RESS["imageVersion"] ?>
    };
</script>


