#!/bin/bash

# Directory containing your PHP classes
SOURCE_DIR="."
OUTPUT_FILE="combined_php_files.txt"

# Clear the output file if it exists
>"$OUTPUT_FILE"

# Loop through all PHP files and append their content to the output file
find "$SOURCE_DIR" -type f -name "*.php" | while read file; do
    echo -e "\n\n/* FILE: $file */\n" >>"$OUTPUT_FILE"
    cat "$file" >>"$OUTPUT_FILE"
done

echo "Combined all PHP files into $OUTPUT_FILE"
