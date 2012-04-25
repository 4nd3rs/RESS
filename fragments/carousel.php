<div class="swipe carousel-inner" id="slider" >
    <ul>
        <li class="item" style='display:block'>
            <img src="img/img1_<?php echo $RESS["imageVersion"]?>.jpg" alt="First Image">

            <div class="swipe-caption">
                <h4>First Thumbnail label</h4>

                <p>Photo: <a href="http://www.flickr.com/photos/me_charlotte/324890685/">Charlotte Hammer</a></p>
            </div>
        </li>
        <li class="item" style='display:none'>
            <img src="img/img2_<?php echo $RESS["imageVersion"]?>.jpg" alt="">

            <div class="swipe-caption">
                <h4>Second Thumbnail label</h4>

                <p>Photo: <a href="http://www.flickr.com/photos/me_charlotte/379088112/">Charlotte Hammer</a></p>
            </div>

        </li>
        <li class="item" style='display:none'>
            <img src="img/img3_<?php echo $RESS["imageVersion"]?>.jpg" alt="">

            <div class="swipe-caption">
                <h4>Third Thumbnail label</h4>

                <p>Photo: <a href="http://www.flickr.com/photos/me_charlotte/366248863/">Charlotte Hammer</a></p>
            </div>
        </li>
    </ul>
    <a class="left carousel-control" href='#' onclick='slider.prev();return false;'>‹</a>
    <a class="right carousel-control" href='#' onclick='slider.next();return false;'>›</a>
</div>