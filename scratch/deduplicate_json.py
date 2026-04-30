import json
import os

def deduplicate_json(file_path):
    if not os.path.exists(file_path):
        print(f"File {file_path} not found.")
        return

    with open(file_path, 'r', encoding='utf-8') as f:
        # Load as a list of tuples to handle duplicates during parsing if needed,
        # but here we just want the final unique keys.
        # json.load() will naturally take the last value for a duplicate key.
        data = json.load(f)

    with open(file_path, 'w', encoding='utf-8') as f:
        json.dump(data, f, ensure_ascii=False, indent=4)
    print(f"Deduplicated {file_path}")

deduplicate_json('/home/mesmat/laravel/stationery-store/lang/en.json')
deduplicate_json('/home/mesmat/laravel/stationery-store/lang/ar.json')
