//set screen width
document.getElementById("width").innerHTML = window.innerWidth;

//detect if touchscreen
var touchElem = document.getElementById("touch");
Modernizr.touch ?  touchElem.innerHTML = "Yes" : touchElem.innerHTML = "No"

//detect if cssanim is supported
var cssanimElem = document.getElementById("cssanim");
Modernizr.cssanimations ?  cssanimElem.innerHTML = "Yes" : cssanimElem.innerHTML = "No";
