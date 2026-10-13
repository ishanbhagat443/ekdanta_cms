// A gzip proxy in front of the preview server, so Lighthouse sees compressed
// responses as a production server sends them (php artisan serve sends none).
//   node tests/visual/gzip-proxy.mjs   → http://127.0.0.1:8002 → http://127.0.0.1:8001
import http from 'node:http';
import zlib from 'node:zlib';

const target = { host: '127.0.0.1', port: 8001 };
http.createServer((req, res) => {
    const upstream = http.request({ ...target, path: req.url, method: req.method, headers: { ...req.headers, host: `${target.host}:${target.port}`, 'accept-encoding': 'identity' } }, (up) => {
        const type = String(up.headers['content-type'] || '');
        const headers = { ...up.headers };
        if (/text|javascript|json|svg|css|xml/.test(type) && /gzip/.test(String(req.headers['accept-encoding']))) {
            delete headers['content-length'];
            headers['content-encoding'] = 'gzip';
            res.writeHead(up.statusCode, headers);
            up.pipe(zlib.createGzip()).pipe(res);
        } else {
            res.writeHead(up.statusCode, headers);
            up.pipe(res);
        }
    });
    upstream.on('error', () => { res.writeHead(502); res.end(); });
    req.pipe(upstream);
}).listen(8002, '127.0.0.1');
