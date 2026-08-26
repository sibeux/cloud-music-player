# File ini dibutuhkan oleh cPanel (Phusion Passenger) untuk menjalankan aplikasi Flask
import sys
import os

# Menambahkan direktori aplikasi ke path sistem Python
sys.path.insert(0, os.path.dirname(__file__))

# Meng-import aplikasi Flask dari app.py
from app import app as application
