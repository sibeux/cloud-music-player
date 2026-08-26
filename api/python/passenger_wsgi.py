import sys
import os
import traceback

sys.path.insert(0, os.path.dirname(__file__))

def application(environ, start_response):
    try:
        from app import app as flask_app
        return flask_app(environ, start_response)
    except Exception as e:
        start_response('500 Internal Server Error', [('Content-Type', 'text/plain')])
        err = "Error saat memuat aplikasi:\n\n" + traceback.format_exc()
        return [err.encode('utf-8')]
