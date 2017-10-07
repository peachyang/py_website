(function (factory) {
    if (typeof define === "function" && define.amd) {
        define(["jquery"], factory);
    } else if (typeof module === "object" && module.exports) {
        module.exports = factory(require(["jquery"]));
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
        if (options.length) {
            $(this.objects[target + '-text']).attr({hidden: 'hidden', disabled: 'disabled'});
            target = this.objects[target];
            $(target).removeAttr('hidden').removeAttr('disabled');
            var fg = document.createDocumentFragment();
            $(fg).append('<option value=""></option>');
            var value = $(target).data('default-value');
            for (var i in options) {
                var oo = document.createElement('option');
                $(oo).attr('value', options[i].value).text(options[i].label);
                if (value && value == options[i].value) {
                    $(oo).attr('selected', 'selected');
                }
                $(fg).append(oo);
            }
            $(target).html(fg);
        } else {
            $(this.objects[target + '-text']).removeAttr('hidden').removeAttr('disabled');
            $(this.objects[target]).attr({hidden: 'hidden', disabled: 'disabled'});
            switch (target) {
                case 'region':
                    $(this.objects['city-text']).removeAttr('hidden').removeAttr('disabled');
                    $(this.objects['city']).attr({hidden: 'hidden', disabled: 'disabled'});
                case 'city':
                    $(this.objects['county-text']).removeAttr('hidden').removeAttr('disabled');
                    $(this.objects['county']).attr({hidden: 'hidden', disabled: 'disabled'});
            }
        }
    };
    Locate.prototype.loadData = function (target, param) {
        var o = this;
        if (window.localStorage && localStorage['locate-' + GLOBAL.LOCALE + param]) {
            o.appendOptions(target, localStorage['locate-' + GLOBAL.LOCALE + param], param);
        } else {
            $.get(o.options.url, param).success(function (response) {
                o.appendOptions(target, response, param);
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
            'country-text': $('#' + o.options.prefix + o.options.countryId + '-text'),
            'region-text': $('#' + o.options.prefix + o.options.regionId + '-text'),
            'city-text': $('#' + o.options.prefix + o.options.cityId + '-text'),
            'county-text': $('#' + o.options.prefix + o.options.countyId + '-text')
        };
        o.loadDefault();
        $(o.objects.country).on('change.seahinet', function () {
            var v = $(this).val();
            if (v) {
                var param = 'region=' + v;
                o.loadData('region', param);
                $(o.objects.region).trigger('change.seahinet');
            } else {
                $(o.objects['region-text']).removeAttr('hidden').removeAttr('disabled');
                $(o.objects['region']).attr({hidden: 'hidden', disabled: 'disabled'});
                $(o.objects['city-text']).removeAttr('hidden').removeAttr('disabled');
                $(o.objects['city']).attr({hidden: 'hidden', disabled: 'disabled'});
                $(o.objects['county-text']).removeAttr('hidden').removeAttr('disabled');
                $(o.objects['county']).attr({hidden: 'hidden', disabled: 'disabled'});
            }
        });
        $(o.objects.region).on('change.seahinet', function () {
            var v = $(this).val();
            if (v) {
                var param = 'city=' + v;
                o.loadData('city', param);
                $(o.objects.city).trigger('change.seahinet');
            } else {
                $(o.objects['city-text']).removeAttr('hidden').removeAttr('disabled');
                $(o.objects['city']).attr({hidden: 'hidden', disabled: 'disabled'});
                $(o.objects['county-text']).removeAttr('hidden').removeAttr('disabled');
                $(o.objects['county']).attr({hidden: 'hidden', disabled: 'disabled'});
            }
        });
        $(o.objects.city).on('change.seahinet', function () {
            var v = $(this).val();
            if (v) {
                var param = 'county=' + v;
                o.loadData('county', param);
                $(o.objects.county).trigger('change.seahinet');
            } else {
                $(o.objects['county-text']).removeAttr('hidden').removeAttr('disabled');
                $(o.objects['county']).attr({hidden: 'hidden', disabled: 'disabled'});
            }
        });
    };
}));