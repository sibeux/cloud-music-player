import sys
import os
import traceback

def application(environ, start_response):
    start_response('200 OK', [('Content-Type', 'text/plain')])
    return [b"Hello dari Passenger! Jika Anda melihat ini, berarti server Python jalan."]
