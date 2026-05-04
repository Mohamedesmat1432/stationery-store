import os
import re

def check_mismatches(directory):
    for root, dirs, files in os.walk(directory):
        for file in files:
            if file.endswith(".vue"):
                path = os.path.join(root, file)
                with open(path, 'r') as f:
                    content = f.read()
                    labels = re.findall(r'<Label\s+[^>]*for="([^"]+)"', content)
                    # Also check lowercase label
                    labels += re.findall(r'<label\s+[^>]*for="([^"]+)"', content)
                    
                    ids = re.findall(r'\s+id="([^"]+)"', content)
                    # Also check :id
                    ids += re.findall(r'\s+:id="([^"]+)"', content)
                    
                    for label_for in labels:
                        # Skip if it's a dynamic for (contains {{ or is a variable)
                        if '{{' in label_for or '`' in label_for:
                            continue
                        
                        # Check if any id matches (literal match or dynamic match)
                        found = False
                        for element_id in ids:
                            if element_id == label_for:
                                found = True
                                break
                            if f"'{label_for}'" in element_id or f'"{label_for}"' in element_id:
                                found = True
                                break
                        
                        if not found:
                            print(f"Mismatch in {path}: label for=\"{label_for}\" has no matching id")

check_mismatches("resources/js")
