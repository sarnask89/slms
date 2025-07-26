#!/bin/bash

# SLMS Release Script
# Usage: ./release.sh <version> [message]

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Check if version is provided
if [ -z "$1" ]; then
    echo -e "${RED}❌ Error: Version number is required${NC}"
    echo -e "${YELLOW}Usage: ./release.sh <version> [message]${NC}"
    echo -e "${YELLOW}Example: ./release.sh 1.0.1 \"Bug fixes and improvements\"${NC}"
    exit 1
fi

VERSION=$1
MESSAGE=${2:-"Release version $VERSION"}
TAG_NAME="slms-$VERSION"

echo -e "${BLUE}🚀 Creating SLMS Release $VERSION${NC}"
echo -e "${YELLOW}Tag: $TAG_NAME${NC}"
echo -e "${YELLOW}Message: $MESSAGE${NC}"
echo ""

# Check if working directory is clean
if [ -n "$(git status --porcelain)" ]; then
    echo -e "${YELLOW}⚠️  Working directory has uncommitted changes${NC}"
    echo -e "${YELLOW}Committing all changes...${NC}"
    git add .
    git commit -m "feat: Prepare for release $VERSION"
fi

# Check if tag already exists
if git tag -l | grep -q "^$TAG_NAME$"; then
    echo -e "${RED}❌ Error: Tag $TAG_NAME already exists${NC}"
    echo -e "${YELLOW}Use a different version number or delete the existing tag${NC}"
    exit 1
fi

# Create tag
echo -e "${BLUE}📝 Creating tag $TAG_NAME...${NC}"
git tag -a "$TAG_NAME" -m "$MESSAGE"

# Push changes
echo -e "${BLUE}📤 Pushing to remote repository...${NC}"
git push origin main
git push origin "$TAG_NAME"

echo ""
echo -e "${GREEN}✅ Release $VERSION created successfully!${NC}"
echo -e "${GREEN}🏷️  Tag: $TAG_NAME${NC}"
echo -e "${GREEN}🔗 GitHub: https://github.com/sarnask89/slms/releases/tag/$TAG_NAME${NC}"

# Show next steps
echo ""
echo -e "${BLUE}📋 Next Steps:${NC}"
echo -e "${YELLOW}1. GitHub Actions will automatically create a release${NC}"
echo -e "${YELLOW}2. Review the release notes on GitHub${NC}"
echo -e "${YELLOW}3. Download the release assets${NC}"
echo -e "${YELLOW}4. Update your deployment${NC}"

# Show recent tags
echo ""
echo -e "${BLUE}📊 Recent Releases:${NC}"
git tag -l "slms-*" --sort=-version:refname | head -5 