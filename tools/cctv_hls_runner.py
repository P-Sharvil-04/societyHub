import os
import subprocess
import time
from pathlib import Path
import mysql.connector
from mysql.connector import Error
import re

# ===== CONFIG =====
DB_CONFIG = {
    "host": "localhost",
    "user": "root",
    "password": "",
    "database": "society_managements"
}

FFMPEG_EXE = r"C:\xampp\htdocs\ffmpeg\bin\ffmpeg.exe"
OUTPUT_ROOT = Path(r"C:\xampp\htdocs\society_management\assets\hls")

# ===== HELPER FUNCTIONS =====
def connect_db():
    try:
        print("[DB] Attempting to connect to MySQL...")
        conn = mysql.connector.connect(**DB_CONFIG)
        if conn.is_connected():
            print("[DB] Connection successful")
            return conn
    except Error as e:
        print(f"[DB] Connection error: {e}")
    return None

def get_active_cameras(conn):
    try:
        cursor = conn.cursor(dictionary=True)
        cursor.execute("""
            SELECT id, name, brand, ip_address, port, username, password, channel
            FROM cctv_cameras
            WHERE is_active=1
        """)
        cameras = cursor.fetchall()
        print(f"[DB] Active cameras found: {len(cameras)}")
        return cameras
    except Exception as e:
        print(f"[DB] Failed to fetch cameras: {e}")
        return []

def safe_name(name):
    """Convert camera name to URL/folder safe string"""
    name = name.lower().strip()
    name = re.sub(r'[^a-z0-9]+', '_', name)  # replace spaces & symbols
    return name.strip('_')

def build_rtsp_url(cam):
    user = cam['username']
    pw = cam['password'].replace('@', '%40')  # URL encode '@'
    ip = cam['ip_address']
    port = cam['port']
    channel = cam.get('channel', 101)
    return f"rtsp://{user}:{pw}@{ip}:{port}/Streaming/Channels/{channel}"

def start_camera_stream(cam):
    camera_name = safe_name(cam['name'])
    rtsp_url = build_rtsp_url(cam)
    
    # Create output folder
    output_path = OUTPUT_ROOT / camera_name
    output_path.mkdir(parents=True, exist_ok=True)
    
    index_file = output_path / "index.m3u8"
    segment_file = output_path / "seg_%05d.ts"
    
    # FFmpeg command
    cmd = [
        FFMPEG_EXE,
        "-hide_banner",
        "-loglevel", "info",
        "-y",
        "-rtsp_transport", "tcp",
        "-i", rtsp_url,
        "-an",
        "-c:v", "libx264",
        "-preset", "veryfast",
        "-tune", "zerolatency",
        "-g", "50",
        "-keyint_min", "50",
        "-sc_threshold", "0",
        "-f", "hls",
        "-hls_time", "2",
        "-hls_list_size", "5",
        "-hls_flags", "delete_segments+omit_endlist+temp_file",
        "-hls_segment_filename", str(segment_file),
        str(index_file)
    ]
    
    try:
        # Start FFmpeg in background (non-blocking)
        proc = subprocess.Popen(cmd, stdout=subprocess.PIPE, stderr=subprocess.PIPE, text=True)
        print(f"[OK] {cam['name']} started → {index_file}")
        return proc
    except Exception as e:
        print(f"[FAIL] {cam['name']} failed to start: {e}")
        return None

# ===== MAIN LOOP =====
if __name__ == "__main__":
    print("=== SCRIPT START ===")
    print(f"OUTPUT_ROOT={OUTPUT_ROOT}")
    print(f"FFMPEG_EXE={FFMPEG_EXE}")

    conn = connect_db()
    if not conn:
        print("[ERROR] Cannot continue without DB")
        exit(1)

    cameras = get_active_cameras(conn)
    processes = []

    for cam in cameras:
        proc = start_camera_stream(cam)
        if proc:
            processes.append((safe_name(cam['name']), proc))

    print("[LOOP] Running stream monitor...")
    try:
        while True:
            for name, proc in processes:
                retcode = proc.poll()
                if retcode is not None:
                    print(f"[FAIL] {name} exited with code {retcode}, restarting...")
                    cam = next(c for c in cameras if safe_name(c['name']) == name)
                    new_proc = start_camera_stream(cam)
                    if new_proc:
                        processes = [(n, p) if n != name else (name, new_proc) for n, p in processes]
            time.sleep(5)
    except KeyboardInterrupt:
        print("[EXIT] Stopping all streams...")
        for _, proc in processes:
            proc.terminate()
