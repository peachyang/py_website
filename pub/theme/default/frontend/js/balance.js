$('.checkout-steps').on('afterajax.seahinet', '[data-load]', function () {
            $(this).siblings('.clicked').removeClass('clicked');
            $(this).toggleClass('clicked');
            var load = $(this).data('load');
            if (typeof load === 'string') {
                load = load.indexOf(',') === -1 ? [load] : eval('(' + load + ')');
            }
            for (var i in load) {
                loadStep(load[i]);
            }
        });