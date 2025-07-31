#!/bin/bash

JS_FILE="../html/webgl_interface.js"
BACKUP_FILE="../html/webgl_interface.js.bak.$(date +%Y%m%d%H%M%S)"

# Backup
cp "$JS_FILE" "$BACKUP_FILE"
echo "Backup saved as $BACKUP_FILE"

# Usuń linie zawierające tylko '...'
sed -i '/^[[:space:]]*\.{3}[[:space:]]*$/d' "$JS_FILE"

# Usuń '...' w środku linii
sed -i 's/\.\.\.//g' "$JS_FILE"

# Usuń puste linie powstałe po usunięciu '...'
sed -i '/^[[:space:]]*$/N;/^\n$/D' "$JS_FILE"

echo "Naprawa zakończona. Sprawdź ../html/webgl_interface.js i odśwież stronę."
