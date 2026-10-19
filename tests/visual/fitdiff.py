"""Compare two fitshots runs: python tests/visual/fitdiff.py <dirA> <dirB>
Lists every shot whose pixels differ (more than a few anti-aliasing pixels)."""
import os
import sys

from PIL import Image, ImageChops

a, b = (os.path.join('tests/visual/shots', d) for d in sys.argv[1:3])
names = sorted(set(os.listdir(a)) | set(os.listdir(b)))
changed = 0
for name in names:
    pa, pb = os.path.join(a, name), os.path.join(b, name)
    if not (os.path.exists(pa) and os.path.exists(pb)):
        print('only in one run:', name)
        changed += 1
        continue
    ia, ib = Image.open(pa).convert('RGB'), Image.open(pb).convert('RGB')
    if ia.size != ib.size:
        print('size differs:', name)
        changed += 1
        continue
    diff = ImageChops.difference(ia, ib).convert('L').point(lambda v: 255 if v > 40 else 0)
    n = sum(1 for v in diff.getdata() if v)
    if n > 30:
        print(f'{name}: {n} pixels, box {diff.getbbox()}')
        changed += 1
print(f'{len(names)} shots, {changed} differ')
