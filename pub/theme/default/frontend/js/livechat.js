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
        var instance = null;
        var ws = function (url) {
            this.url = url;
            this.retry = 0;
            this.connect();
        };
        ws.prototype = {
            check: function () {
                return this.socket && this.socket.readyState == 1;
            },
            ping: function () {
                var buffer = new ArrayBuffer(2);
                var i8V = new Int8Array(buffer);
                i8V[0] = 0x9;
                i8V[1] = 0;
                if (instance.check()) {
                    instance.socket.send(buffer);
                }
                instance.pingTimeout = setTimeout(instance.ping, 60000);
            },
            connect: function () {
                try {
                    instance.lock = false;
                    if (instance.check()) {
                        return;
                    }
                    instance.socket = new WebSocket(this.url);
                    instance.queue = [];
                    instance.socket.onopen = this.onopen;
                    instance.socket.onmessage = this.onmessage;
                    instance.socket.onclose = this.onclose;
                    instance.socket.onerror = function (e) {
                        console.log(e);
                    };
                } catch (e) {
                    instance.reconnect.call(instance);
                }
            },
            reconnect: function () {
                if (!this.lock || this.retry++ <= 5) {
                    this.lock = true;
                    setTimeout(instance.connect, 2000 * Math.pow(2, this.retry));
                    this.connect(this.url);
                }
            },
            onopen: function () {
                this.retry = 0;
                $(this).trigger('opened.livechat');
                this.pingTimeout = setTimeout(instance.ping, 60000);
            },
            onmessage: function (e) {
                var data = e.data;
                if (typeof data === 'string') {
                    data = eval('(' + data + ')');
                }
                if (data.new) {
                    $('#livechat').trigger('new.livechat', data.new);
                } else {
                    o.log(data);
                }
            },
            onclose: function () {
                this.reconnect.call(this);
            },
            send: function (m) {
                this.socket.send(m);
                clearTimeout(this.pingTimeout);
                this.pingTimeout = setTimeout(instance.ping, 60000);
            },
            log: function (data) {
                var c = data.sender == msg.sender ? 'self' : 'others';
                var t = data.type;
                var m = data.msg;
                if (t === 'image') {
                    m = '<img src="' + m + '" />';
                } else if (t === 'audio') {
                    m = '<audio controls="controls" src="' + m + '" />'
                }
                $('#livechat #' + data.session + ' .chat-list').append($('<li class="' + c + '">' + m + '</li>'));
                if (localStorage[data.session]) {
                    var r = JSON.parse(localStorage[data.session]);
                    r.push({class: c, msg: m});
                    localStorage[data.session] = JSON.stringify(r);
                } else {
                    localStorage[data.session] = JSON.stringify([{class: c, msg: m}]);
                }
                $('#livechat #' + data.session + ' .chat-history').scrollTop($('#livechat #' + data.session + ' .chat-list').height());
                $('#livechat').trigger('notify', t === 'text' ? m : '');
                return false;
            }
        };
        var notify = function (e, t) {
            if (Notification.permission === 'granted') {
                var n = new Notification(translate('You have received a new message.'), {
                    body: t
                });
                setTimeout(function () {
                    n.close();
                }, 3000);
            }
        };
        var init = function () {
            var s = $('#livechat #chat-form [name=sender]');
            msg = {
                session: '',
                sender: $(s).val(),
                type: 'text',
                msg: ''
            }
            $(s).remove();
            instance = new ws($('#livechat #chat-form').attr('action'));
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
            var o = $('#livechat #' + session + ' [name=msg]');
            msg.session = session;
            msg.type = 'text';
            msg.msg = $(o).val();
            $(o).val('');
            if (instance && instance.check()) {
                instance.send(JSON.stringify(msg));
            } else {
                init();
                $(instance).one('opened', function () {
                    instance.send(JSON.stringify(msg));
                });
            }
        };
        var sendBin = function (session, type) {
            var o = $('#livechat #' + session + ' [type=file].send-' + type)[0];
            var reader = new FileReader();
            reader.readAsDataURL(o.files[0]);
            reader.onload = function () {
                msg.session = session;
                msg.type = type;
                msg.msg = this.result;
                if (instance && instance.check()) {
                    instance.send(JSON.stringify(msg));
                } else {
                    init();
                    $(instance).one('opened', function () {
                        this.send(JSON.stringify(msg));
                    });
                }
            };
        };
        var ids = '[';
        $('#livechat .tab-pane [name=session]').each(function () {
            $(this).siblings('[name=sender]').remove();
            ids += '"' + this.value + '",';
        });
        init();
        $(instance).on('opened.livechat', function () {
            this.send('{"sender":' + msg.sender + ',"init":' + ids.replace(/\,$/, ']') + '}');
        });
        $('#livechat #chat-form').on('click', '[type=submit]', function () {
            var session = $(this).val();
            send(session);
            return false;
        }).on('change', '.send-image', function () {
            var session = $(this).parents('.tab-pane').first().find('[name=session]').val();
            sendBin(session, 'image');
        }).on('change', '.send-audio', function () {
            var session = $(this).parents('.tab-pane').first().find('[name=session]').val();
            sendBin(session, 'audio');
        });
    });
}));
