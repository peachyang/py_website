(function (factory) {
    if (typeof define === "function" && define.amd) {
        define(["jquery", "jquery.ui.sortable"], factory);
    } else if (typeof module === "object" && module.exports) {
        module.exports = factory(require(["jquery", "jquery.ui.sortable"]));
    } else {
        factory(jQuery);
    }
}(function ($) {
    "use strict";
    window.Locate = function (options) {
        this.options = $.extend({}, {
            countryId: 'country',
            regionId: 'region',
            cityId: 'city',
            countyId: 'county',
            prefix: '',
            url: GLOBAL.BASE_URL + 'i18n/locate/'
        }, options);
        this.init.apply(this);
    };
    Locate.prototype.appendOptions = function (target, response, param) {
        if (window.localStorage && !localStorage['locate-' + GLOBAL.LOCALE + param]) {
            localStorage['locate-' + GLOBAL.LOCALE + param] = JSON.stringify(response);
        }
        var options = typeof response === 'string' ? eval('(' + response + ')') : response;
        var fg = document.createDocumentFragment();
        $(fg).append('<option value=""></option>');
        var value = $(target).data('default-value');
        for (var i in options) {
            $(fg).append('<option value="' + options[i].value + '"' +
                    (value && value == options[i].value ?
                            ' selected="selected"' : '')
                    + '>' + options[i].label + '</option>');
        }
        $(target).html(fg);
    };
    Locate.prototype.loadData = function (target, param) {
        var o = this;
        if (window.localStorage && localStorage['locate-' + GLOBAL.LOCALE + param]) {
            o.appendOptions(o.objects[target], localStorage['locate-' + GLOBAL.LOCALE + param], param);
        } else {
            $.get(o.options.url, param).success(function (response) {
                o.appendOptions(o.objects[target], response, param);
            });
        }
    };
    Locate.prototype.objects = {};
    Locate.prototype.options = {};
    Locate.prototype.loadDefault = function () {
        if ($(this.objects.country).is('select')) {
            this.loadData('country', '');
            var country = $(this.objects.country).data('default-value');
        } else {
            var country = $(this.objects.country).val();
        }
        if (country) {
            var param = 'region=' + country;
            this.loadData('region', param);
        }
        var region = $(this.objects.region).data('default-value');
        if (region) {
            var param = 'city=' + region;
            this.loadData('city', param);
        }
        var city = $(this.objects.city).data('default-value')
        if (city) {
            var param = 'county=' + city;
            this.loadData('county', param);
        }
    };
    Locate.prototype.init = function () {
        var o = this;
        o.objects = {
            country: $('#' + o.options.prefix + o.options.countryId),
            region: $('#' + o.options.prefix + o.options.regionId),
            city: $('#' + o.options.prefix + o.options.cityId),
            county: $('#' + o.options.prefix + o.options.countyId),
        };
        o.loadDefault();
        $(o.objects.country).change(function () {
            var param = 'region=' + $(this).val();
            o.loadData('region', param);
            $(o.objects.city).html('');
            $(o.objects.county).html('');
        });
        $(o.objects.region).change(function () {
            var param = 'city=' + $(this).val();
            o.loadData('city', param);
            $(o.objects.county).html('');
        });
        $(o.objects.city).change(function () {
            var param = 'county=' + $(this).val();
            o.loadData('county', param);
        });
    };
}));