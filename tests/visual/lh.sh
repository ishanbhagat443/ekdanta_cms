#!/bin/sh
# Lighthouse mobile performance for a page (default: home), through the gzip
# proxy (tests/visual/gzip-proxy.mjs on :8002), as a production server serves it.
#   sh tests/visual/lh.sh [/path] [name]
P="${1:-/}"; N="${2:-home}"
npx lighthouse "http://127.0.0.1:8002$P" --form-factor=mobile --only-categories=performance \
    --output=json --output-path="tests/visual/shots/lh-$N.json" --quiet --chrome-flags="--headless=new" >/dev/null 2>&1
node -e "const r=require('./tests/visual/shots/lh-$N.json');const a=r.audits;console.log('$N', Math.round(r.categories.performance.score*100), 'FCP', a['first-contentful-paint'].displayValue, 'LCP', a['largest-contentful-paint'].displayValue, 'TBT', a['total-blocking-time'].displayValue, 'CLS', a['cumulative-layout-shift'].displayValue, 'bytes', a['total-byte-weight'].displayValue)"
