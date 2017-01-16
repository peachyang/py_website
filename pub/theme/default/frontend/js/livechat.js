(function (factory) {
    if (typeof define === "function" && define.amd) {
        define(["jquery"], factory);
    } else if (typeof module === "object" && module.exports) {
        module.exports = factory(require("jquery"));
    } else {
        factory(jQuery);
    }
}(function ($) {
    $(function () {
        "use strict";
        var ws = function (url, from, to) {
            this.connect(url);
            this.from = from;
            this.to = to;
        };
        ws.prototype.socket = null;
        ws.prototype.from = null;
        ws.prototype.to = null;
        ws.prototype.check = function () {
            return this.socket != null && this.socket.readyState == 1;
        };
        ws.prototype.connect = function (url) {
            if (this.check()) {
                return;
            }
            var o = this;
            this.socket = new WebSocket(url);
            this.socket.onopen = function () {
                o.socket.send('{"from":"' + o.from + '"}');
                $(o).trigger('opened');
            };
            this.socket.onmessage = function (e) {
                o.log(e.data);
            };
            this.socket.onerror = function (e) {
                console.log("error " + e.data);
            };
            this.socket.onclose = function (e) {
                instance = null;
            };
        };
        ws.prototype.send = function (m) {
            this.socket.send(m);
        };
        ws.prototype.log = function (t, c, r) {
            $('#livechat .chat-list').append($('<li class="' + (c ? c : 'others') + '">' + t + '</li>'));
            if (!r) {
                if (localStorage[this.to]) {
                    localStorage[this.to] = JSON.stringify(JSON.parse(localStorage[this.to]).push({class: c, msg: t}));
                } else {
                    localStorage[this.to] = JSON.stringify({class: c, msg: t});
                }
            }
            $('#livechat .chat-history').scrollTop($('#livechat .chat-list').height());
            $('#livechat').trigger('notify', t);
            return false;
        };
        var instance = [];
        var notify = function (e, t) {
            var n = new Notification(translate('You have received a new message.'), {
                body: t
            });
            setTimeout(function () {
                n.close();
            }, 3000);
        };
        var init = function (from, to) {
            instance = new ws($('#livechat .chat-form').attr('action'), from, to);
            if (Notification.permission !== 'granted') {
                Notification.requestPermission();
            }
            if (localStorage.to) {
                $(JSON.parse(localStorage.to)).each(function () {
                    instance.log(this.msg, this.class, true);
                });
            }
        };
        $(window).on({
            focus: function () {
                $('#livechat').off('notify');
            },
            blur: function () {
                $('#livechat').on('notify', notify);
            }
        });
        var send = function (to) {
            var data = {};
            to = to ? to : this.to;
            $('#livechat #' + to + ' .chat-form [name]').each(function () {
                data[$(this).attr('name')] = $(this).val();
            });
            instance.send(JSON.stringify(data));
            instance.log(data.msg, 'self');
            $('#livechat #' + to + '.chat-form [name=msg]').val('');
        };
        $('#livechat').on('addchatform', function (e, t) {
            var oa = document.createElement('a');
            $(oa).attr({href: '#' + t, 'data-toggle': 'tab'}).html(t);
            var ol = document.createElement('li');
            $(ol).append(oa);
            $('.nav', this).append(ol);
            if ("WebSocket" in window) {
                $('.tab-content', this).append($('#tmpl-livechat-chatform').html().replace(/\{\{\$to\}\}/g, t));
            } else {
                $('.tab-content', this).append($('#tmpl-livechat-mailform').html().replace(/\{\{\$to\}\}/g, t));
            }
            $(oa).tab('show');
        });
        $('#livechat').on('submit', '.chat-form', function () {
            var from = $('[name=from]', this).val();
            var to = $('[name=to]', this).val();
            if (typeof instance[to] === undefined) {
                init(from, to);
                $(instance[to]).one('opened', send);
            } else {
                send(to);
            }
            return false;
        });
    });
}));
