<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link href="{{asset('css/terminal/xterm.min.css')}}" rel="stylesheet" type="text/css"/>
</head>

<body onload="start()">
<div id="terminal"></div>
@php( $data = request()->only('server_id','ip_address','token','connection_type','connection_port'))
<script src="{{asset('js/xterm.min.js')}}"></script>
<script>
function ab2str(buf) {
		return String.fromCharCode.apply(null, new Uint8Array(buf));
}
   
function start() {
    let data  = @json($data);
    let url = new URL('wss://' + window.location.hostname + '/webssh');
    url.searchParams.append('ip_address',data['ip_address'] || "");
    url.searchParams.append('server_id',data['server_id'] || "");
    url.searchParams.append('token',data['token'] || "");
    url.searchParams.append('connection_type',data['connection_type'] || "");
    url.searchParams.append('connection_port',data['connection_port'] || "");
    let sock = new window.WebSocket(url),
        terminal = document.getElementById('#terminal'),
        term = new window.Terminal({
            cursorBlink: true,
            rows : 30,
            screenKeys: true
        });
    sock.binaryType = "arraybuffer";
    
    term.on('data', function (data) {
        sock.send(new TextEncoder().encode("\x00" + data));
    });

    term.on('paste', function (data, ev) {
        term.write(data);
    });

    sock.onopen = function () {
        term.open(terminal, true);
    };

    sock.onmessage = function (msg) {
        if (msg.data instanceof ArrayBuffer) {
			term.write(ab2str(msg.data));
		} else {
			alert(msg.data)
		}
    };

    sock.onerror = function (e) {
        console.error(e);
    };

    sock.onclose = function (e) {
        term.destroy();
        term = undefined;
        sock = undefined;
    };

}

</script>
</body>
</html>