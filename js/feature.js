$("#width").html(window.innerWidth);

Modernizr.touch ?  $("#touch").html("Yes") : $("#touch").html("No");

Modernizr.cssanimations ?  $("#cssanim").html("Yes") : $("#cssanim").html("No");

Modernizr.video.h264 ?  $("#h264").html(Modernizr.video.h264) : $("#h264").html("No");
