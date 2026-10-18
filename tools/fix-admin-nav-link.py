import re
import pathlib

path = pathlib.Path("resources/views/layouts/admin.blade.php")
text = path.read_text(encoding="utf-8")
text = text.replace(" ? 'active' : ''", "")
path.write_text(text, encoding="utf-8")
print("done")
