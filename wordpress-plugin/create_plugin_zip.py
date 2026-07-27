import os
import zipfile

def build_plugin_zip():
    base_dir = os.path.dirname(os.path.abspath(__file__))
    plugin_folder = os.path.join(base_dir, 'scribd-downloader')
    output_zip = os.path.join(base_dir, 'scribd-downloader.zip')

    with zipfile.ZipFile(output_zip, 'w', zipfile.ZIP_DEFLATED) as zipf:
        for root, dirs, files in os.walk(plugin_folder):
            for file in files:
                file_path = os.path.join(root, file)
                rel_path = os.path.relpath(file_path, base_dir)
                zipf.write(file_path, rel_path)
                print(f"Added {rel_path} to zip")

    print(f"\nSUCCESS: Plugin zip package created at: {output_zip}")

if __name__ == '__main__':
    build_plugin_zip()
