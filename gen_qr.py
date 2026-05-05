#!/usr/bin/env python3
import sys
import qrcode
import base64
from io import BytesIO

text = sys.argv[1] if len(sys.argv) > 1 else 'MINICINE'

qr = qrcode.QRCode(
    version=2,
    error_correction=qrcode.constants.ERROR_CORRECT_M,
    box_size=8,
    border=4
)
qr.add_data(text)
qr.make(fit=True)
img = qr.make_image(fill_color='#1a1a1a', back_color='white')

buf = BytesIO()
img.save(buf, format='PNG')
b64 = base64.b64encode(buf.getvalue()).decode()
print('data:image/png;base64,' + b64)