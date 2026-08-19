#!/bin/bash
set -euo pipefail

# Dynamically find the project root and load .env
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" &> /dev/null && pwd)"
ENV_FILE="$SCRIPT_DIR/../.env"

if [[ -f "$ENV_FILE" ]]; then
    set -a
    source "$ENV_FILE"
    set +a
else
    echo "ERROR: .env file not found at $ENV_FILE"
    exit 1
fi

if [[ -z "${DOC_ROOT:-}" ]]; then
    echo "ERROR: DOC_ROOT is not set in .env"
    exit 1
fi

if [[ -z "${REPO_PUBLIC:-}" ]]; then
    echo "ERROR: REPO_PUBLIC is not set in .env"
    exit 1
fi

declare -i count_created=0
declare -i count_correct=0
declare -i count_conflicts=0
declare -i count_errors=0

echo "========================================"
echo "Deployment Symlink Check"
echo "========================================"
echo "Document Root: $DOC_ROOT"
echo "Repository Public: $REPO_PUBLIC"
echo "----------------------------------------"

# 1. Validate directories exist
if [[ ! -d "$DOC_ROOT" ]]; then
    echo "ERROR: Document root does not exist: $DOC_ROOT"
    exit 1
fi

if [[ ! -d "$REPO_PUBLIC" ]]; then
    echo "ERROR: Repository public directory does not exist: $REPO_PUBLIC"
    exit 1
fi

# 2. Iterate over the contents of the public repository directory
# We use shopt to ensure dotfiles are also matched if any exist in public/
shopt -s nullglob dotglob
for source_item in "$REPO_PUBLIC"/*; do
    item_name=$(basename "$source_item")
    
    # Skip any '.' or '..' that might slip through in older bashes (though dotglob usually excludes them)
    if [[ "$item_name" == "." || "$item_name" == ".." ]]; then
        continue
    fi

    target_item="$DOC_ROOT/$item_name"

    # Explicit protection for the existing /api symlink in case it is mistakenly placed in public/
    if [[ "$item_name" == "api" ]]; then
        echo "WARNING: 'api' found in repository public directory. Skipping to strictly preserve the existing Document Root API symlink."
        continue
    fi

    # Check the state of the target item
    if [[ -L "$target_item" ]]; then
        current_target=$(readlink "$target_item")
        
        if [[ ! -e "$target_item" ]]; then
            echo "CONFLICT: '$item_name' is a broken symlink."
            echo "  - Existing path: $target_item"
            echo "  - Path type: broken symlink"
            echo "  - Current target: $current_target"
            echo "  - Expected target: $source_item"
            echo "  - Recommended action: Remove the broken symlink manually and re-run this script."
            count_conflicts+=1
        elif [[ "$current_target" == "$source_item" ]]; then
            echo "OK: '$item_name' is already correctly symlinked."
            count_correct+=1
        else
            echo "CONFLICT: '$item_name' points to the wrong target."
            echo "  - Existing path: $target_item"
            echo "  - Path type: symlink"
            echo "  - Current target: $current_target"
            echo "  - Expected target: $source_item"
            echo "  - Recommended action: Verify and remove the incorrect symlink manually, then re-run."
            count_conflicts+=1
        fi
    elif [[ -e "$target_item" ]]; then
        if [[ -d "$target_item" ]]; then
            path_type="directory"
        else
            path_type="regular file"
        fi
        echo "CONFLICT: '$item_name' exists and is not a symlink."
        echo "  - Existing path: $target_item"
        echo "  - Path type: $path_type"
        echo "  - Expected target: $source_item"
        echo "  - Recommended action: Determine if the existing item can be safely deleted or backed up, remove it manually, then re-run."
        count_conflicts+=1
    else
        echo "Creating symlink for '$item_name'..."
        if ln -s "$source_item" "$target_item"; then
            echo "  -> Success"
            count_created+=1
        else
            echo "  -> ERROR: Failed to create symlink for '$item_name'"
            count_errors+=1
        fi
    fi
done
shopt -u nullglob dotglob

echo "----------------------------------------"
echo "Deployment Summary:"
echo "  Created         : $count_created"
echo "  Already correct : $count_correct"
echo "  Conflicts       : $count_conflicts"
echo "  Errors          : $count_errors"
echo "========================================"

if [[ $count_conflicts -gt 0 || $count_errors -gt 0 ]]; then
    echo "Deployment incomplete due to conflicts or errors. Please manually resolve the reported conflicts and try again."
    exit 1
else
    echo "Deployment checks completed successfully."
fi
