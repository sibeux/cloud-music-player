from flask import Flask, request, jsonify
import subprocess
import json

app = Flask(__name__)

@app.route('/')
def index():
    # Route ini ditambahkan khusus agar fitur cPanel "Check availability" 
    # mendeteksi respon 200 OK dan tidak memunculkan pesan error 404.
    return "Python API is running!", 200

@app.route('/api/check_codec', methods=['POST'])
def check_codec():
    data = request.get_json()
    
    if not data or 'file_url' not in data:
        return jsonify({"error": "Missing 'file_url' parameter"}), 400

    file_url = data['file_url']
    ffprobe_path = data.get('ffprobe_path', 'ffprobe')
    
    command = [
        ffprobe_path,
        "-v", "error",
        "-show_streams",
        "-show_format",
        "-print_format", "json",
        file_url
    ]
    
    try:
        # Menjalankan ffprobe command
        result = subprocess.run(command, capture_output=True, text=True, encoding='utf-8', errors='replace', timeout=60)
        
        if result.returncode != 0:
            return jsonify({
                "error": "ffprobe command failed",
                "stderr": result.stderr,
                "stdout": result.stdout
            }), 500
            
        metadata = json.loads(result.stdout)
        return jsonify(metadata), 200

    except subprocess.TimeoutExpired:
        return jsonify({"error": "ffprobe command timed out"}), 504
    except json.JSONDecodeError:
        return jsonify({
            "error": "Failed to parse ffprobe JSON output",
            "stdout": result.stdout
        }), 500
    except Exception as e:
        return jsonify({"error": str(e)}), 500

if __name__ == '__main__':
    # Menjalankan pada port 5000 (bisa disesuaikan)
    app.run(host='127.0.0.1', port=5000)
