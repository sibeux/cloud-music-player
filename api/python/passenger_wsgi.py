import sys
import os
import traceback

# Menambahkan direktori aplikasi ke path sistem Python
sys.path.insert(0, os.path.dirname(__file__))

try:
    # Meng-import aplikasi Flask dari app.py
    from app import app as application
except Exception as e:
    # Jika terjadi error saat memuat app (misalnya Flask belum terinstall),
    # error akan ditampilkan langsung ke layar browser untuk mempermudah perbaikan.
    def application(environ, start_response):
        start_response('500 Internal Server Error', [('Content-Type', 'text/plain')])
        error_msg = "Error saat memuat aplikasi Flask:\n\n" + traceback.format_exc()
        return [error_msg.encode('utf-8')]
