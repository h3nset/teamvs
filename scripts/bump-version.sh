#!/bin/bash
# Version bump script for TeamVS project
# Usage: ./scripts/bump-version.sh [major|minor|patch]

TYPE=${1:-patch}
ENV_FILE=".env"
PROJECT_DIR="/var/www/php82/teamvs"

cd "$PROJECT_DIR" || exit 1

# Check if .env exists
if [ ! -f "$ENV_FILE" ]; then
    echo "❌ .env file not found!"
    exit 1
fi

# Get current version
CURRENT_VERSION=$(grep "APP_VERSION=" "$ENV_FILE" | cut -d '=' -f2 | tr -d '"' | tr -d "'")

if [ -z "$CURRENT_VERSION" ]; then
    echo "❌ APP_VERSION not found in .env"
    echo "Add this line to .env: APP_VERSION=1.0.0"
    exit 1
fi

# Parse version
IFS='.' read -ra VERSION <<< "$CURRENT_VERSION"
MAJOR=${VERSION[0]}
MINOR=${VERSION[1]}
PATCH=${VERSION[2]}

# Bump version based on type
case $TYPE in
    major)
        MAJOR=$((MAJOR + 1))
        MINOR=0
        PATCH=0
        echo "🚀 Bumping MAJOR version (breaking changes)"
        ;;
    minor)
        MINOR=$((MINOR + 1))
        PATCH=0
        echo "✨ Bumping MINOR version (new features)"
        ;;
    patch)
        PATCH=$((PATCH + 1))
        echo "🔧 Bumping PATCH version (bug fixes)"
        ;;
    *)
        echo "❌ Invalid version type: $TYPE"
        echo "Usage: ./scripts/bump-version.sh [major|minor|patch]"
        exit 1
        ;;
esac

NEW_VERSION="$MAJOR.$MINOR.$PATCH"
TODAY=$(date +%Y-%m-%d)

echo ""
echo "Current version: $CURRENT_VERSION"
echo "New version:     $NEW_VERSION"
echo "Release date:    $TODAY"
echo ""

# Update .env
sed -i.bak "s/APP_VERSION=.*/APP_VERSION=$NEW_VERSION/" "$ENV_FILE"
sed -i.bak "s/APP_VERSION_DATE=.*/APP_VERSION_DATE=$TODAY/" "$ENV_FILE"

# Remove backup file
rm -f "$ENV_FILE.bak"

echo "✓ Updated .env"
echo ""
echo "Next steps:"
echo "  1. Update CHANGELOG.md with release notes"
echo "  2. Test the application"
echo "  3. Commit: git commit -am 'Release v$NEW_VERSION'"
echo "  4. Tag:    git tag -a v$NEW_VERSION -m 'Release v$NEW_VERSION'"
echo "  5. Push:   git push origin dev && git push --tags"
echo "  6. Merge to main when ready for production"
