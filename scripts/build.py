#!/usr/bin/env python3
"""Build a reproducible, allowlisted WordPress install ZIP without development files."""
from pathlib import Path
from zipfile import ZIP_DEFLATED, ZipFile, ZipInfo

root = Path(__file__).resolve().parents[1]
target = root.parent / (root.name + '.zip')
files = [root / name for name in ('blue-one-riyal-coupons.php', 'readme.txt', 'LICENSE.txt')]
for directory in ('includes', 'assets'):
    files.extend(path for path in (root / directory).rglob('*') if path.is_file())
with ZipFile(target, 'w', ZIP_DEFLATED) as archive:
    for path in sorted(files):
        info = ZipInfo(str(Path(root.name) / path.relative_to(root)), (2026, 10, 13, 0, 0, 0))
        info.compress_type = ZIP_DEFLATED
        info.external_attr = 0o100644 << 16
        archive.writestr(info, path.read_bytes())
with ZipFile(target) as archive:
    assert archive.testzip() is None
print(target)
