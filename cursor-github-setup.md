# 🚀 GitHub Plugins Setup for Cursor

## 📋 Prerequisites
- Cursor installed and running
- GitHub account connected
- Git repository with remote origin

## 🔧 Installation Steps

### 1. Open Cursor Extensions
- Press `Ctrl+Shift+X` (or `Cmd+Shift+X` on Mac)
- Search for "GitHub"

### 2. Install Recommended Extensions

#### GitHub Pull Requests and Issues
```bash
# Extension ID: GitHub.vscode-pull-request-github
# Features:
# - View and manage pull requests
# - Create and track issues
# - Review code changes inline
# - Merge PRs directly from Cursor
```

#### GitHub Repositories
```bash
# Extension ID: GitHub.remoteHub
# Features:
# - Browse GitHub repositories
# - Clone repositories directly
# - View file history and blame
```

#### GitHub Actions
```bash
# Extension ID: GitHub.vscode-github-actions
# Features:
# - View workflow runs
# - Trigger workflows
# - Debug failed builds
```

### 3. Authentication
1. Click "Sign in to GitHub" when prompted
2. Authorize Cursor to access your GitHub account
3. Grant necessary permissions

## 🎯 Usage with Your SLMS Project

### Viewing Releases
- Go to Source Control panel (`Ctrl+Shift+G`)
- Click on "GitHub" tab
- View your `slms-1.0.0` release

### Creating Issues
- Press `Ctrl+Shift+P`
- Type "GitHub: Create Issue"
- Fill in issue details

### Managing Pull Requests
- Press `Ctrl+Shift+P`
- Type "GitHub: Create Pull Request"
- Or view existing PRs in the GitHub panel

## 🔗 Integration with Your Version Tags

### View Release Tags
```bash
# In Cursor terminal
git tag -l
git show slms-1.0.0
```

### Create New Release
```bash
# 1. Make changes and commit
git add .
git commit -m "feat: New feature for 1.0.1"

# 2. Create tag
git tag -a slms-1.0.1 -m "Release version 1.0.1"

# 3. Push tag
git push origin slms-1.0.1
```

### GitHub Release Notes
- Go to your GitHub repository
- Click "Releases" on the right sidebar
- Click "Create a new release"
- Select your tag (e.g., `slms-1.0.1`)
- Add release notes and description

## 🛠️ Advanced Features

### GitHub Actions Integration
Create `.github/workflows/release.yml`:
```yaml
name: Release Workflow
on:
  push:
    tags:
      - 'slms-*'

jobs:
  release:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v2
      - name: Create Release
        uses: actions/create-release@v1
        env:
          GITHUB_TOKEN: ${{ secrets.GITHUB_TOKEN }}
        with:
          tag_name: ${{ github.ref }}
          release_name: Release ${{ github.ref }}
          body: |
            Changes in this Release:
            - ML System integration
            - MCP Server for Copilot
            - Enhanced documentation
          draft: false
          prerelease: false
```

### Automated Versioning
Create a script for automated releases:
```bash
#!/bin/bash
# release.sh
VERSION=$1
if [ -z "$VERSION" ]; then
    echo "Usage: ./release.sh <version>"
    exit 1
fi

git add .
git commit -m "feat: Release version $VERSION"
git tag -a "slms-$VERSION" -m "Release version $VERSION"
git push origin main
git push origin "slms-$VERSION"
echo "Release $VERSION created and pushed!"
```

## 📊 Benefits for Your SLMS Project

1. **Visual Release Management**: See all releases in GitHub interface
2. **Issue Tracking**: Track bugs and feature requests
3. **Pull Request Reviews**: Code review workflow
4. **Automated Releases**: CI/CD integration
5. **Version History**: Complete audit trail

## 🎉 Next Steps

1. Install the recommended GitHub extensions
2. Connect your GitHub account
3. Explore the GitHub panel in Cursor
4. Create your first issue or PR
5. Set up automated release workflows 