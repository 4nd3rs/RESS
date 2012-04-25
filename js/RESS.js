RESS = {};

RESS.isResizeActive = false;

RESS.storeSizes = function (values) {
    //Set new cookie with RESS values


    console.log("Storing new sizes: " + document.documentElement.clientWidth);

    var date = new Date()
    date.setFullYear(date.getFullYear() + 1);
    document.cookie = 'RESS=width.' + width + '; expires=' + date.toUTCString() + '; path=/;';

}

window.onresize = function (event) {
    console.log("Resize event sent");
    if (!RESS.isResizeActive) {
        RESS.isResizeActive = true;

        //make sure we do not do this too often...
        window.setTimeout(function () {
            RESS.storeSizes();
            RESS.isResizeActive = false;
        }, 1000);
    }


}