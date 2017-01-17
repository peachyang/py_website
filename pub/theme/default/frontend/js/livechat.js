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
        var msg = null;
        var ws = function (url, session) {
            this.connect(url);
            this.session = session;
        };
        ws.prototype.socket = null;
        ws.prototype.session = null;
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
                o.socket.send('{"session":"' + o.session + '"}');
                $(o).trigger('opened');
            };
            this.socket.onmessage = function (e) {
                o.log(e.data);
            };
            this.socket.onerror = function (e) {
                console.log("error " + e.data);
            };
            this.socket.onclose = function (e) {
                instance[this.session] = null;
            };
        };
        ws.prototype.send = function (m) {
            this.socket.send(m);
        };
        ws.prototype.log = function (data) {
            if (typeof data === 'string') {
                data = eval('(' + data + ')');
            }
            var c = data.sender == msg.sender ? 'self' : 'others';
            var t = data.type;
            var m = data.msg;
            if (t === 'image') {
                m = '<img src="' + m + '" />';
            } else if (t === 'audio') {
                m = '<audio controls="controls" src="' + m + '" />'
            }
            $('#livechat .chat-list').append($('<li class="' + c + '">' + m + '</li>'));
            if (localStorage[this.session]) {
                var r = JSON.parse(localStorage[this.session]);
                r.push({class: c, msg: m});
                localStorage[this.session] = JSON.stringify(r);
            } else {
                localStorage[this.session] = JSON.stringify([{class: c, msg: m}]);
            }
            $('#livechat .chat-history').scrollTop($('#livechat .chat-list').height());
            $('#livechat').trigger('notify', t === 'text' ? m : '');
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
        var init = function (session) {
            instance[session] = new ws($('#livechat .chat-form').attr('action'), session);
            if (Notification.permission !== 'granted') {
                Notification.requestPermission();
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
        var send = function (session) {
            var o = $('#livechat #' + session + ' .chat-form [name=msg]');
            msg.session = session;
            msg.type = 'text';
            msg.msg = $(o).val();
            $(o).val('');
            if (typeof instance[session] !== 'undefined' && instance[session].check()) {
                instance[session].send(JSON.stringify(msg));
            } else {
                init(session);
                $(instance[session]).one('opened', function () {
                    instance[session].send(JSON.stringify(msg));
                });
            }
        };
        var sendBin = function (session, type) {
            var o = $('#livechat #' + session + ' .chat-form [type=file].send-' + type)[0];
            var reader = new FileReader();
            reader.readAsDataURL(o.files[0]);
            reader.onload = function () {
                msg.session = session;
                msg.type = type;
                msg.msg = this.result;
                if (typeof instance[session] !== 'undefined' && instance[session].check()) {
                    instance[session].send(JSON.stringify(msg));
                } else {
                    init(session);
                    $(instance[session]).one('opened', function () {
                        instance[session].send(JSON.stringify(msg));
                    });
                }
            };
        };
        $('#livechat .tab-pane.active [name=session]').each(function () {
            msg = {
                session: '',
                sender: $(this).siblings('[name=sender]').val(),
                type: 'text',
                msg: ''
            }
            $(this).siblings('[name=sender]').remove();
            init(this.value);
        });
        $('#livechat').on('submit', '.chat-form', function () {
            var session = $('[name=session]', this).val();
            send(session);
            return false;
        }).on('show.bs.tab', '[data-toggle=tab]', function (e) {
            init($('[name=session]', e.target).val());
        }).on('change', '.send-image', function () {
            var session = $(this).parents('.chat-form').first().find('[name=session]').val();
            sendBin(session, 'image');
        }).on('change', '.send-audio', function () {
            var session = $(this).parents('.chat-form').first().find('[name=session]').val();
            sendBin(session, 'audio');
        });
    });
}));
