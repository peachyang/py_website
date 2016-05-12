window.translate = function () {
    "use strict";
    var args = arguments;
    if (args.length > 1) {
        this.data[args[0]] = args[1];
    } else if (typeof args[0] === 'object') {
        this.data = args[0];
    } else {
        var text = args[0];
        if (this.data && this.data[text]) {
            return this.data[text];
        }
        return text;
    }
};