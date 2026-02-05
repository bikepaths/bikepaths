# Bikepaths

A hybrid digital garden exploring the intersections of society, technology, and the human mind.

## Architecture

This repository is a **Hybrid Headless CMS**:
- **Content**: The `blog/` directory acts as the "database." It is a direct mirror of the source of truth from the Bikepaths server.
- **Code**: The root directory contains a Next.js (clean, minimalistic) web application that parses the markdown content at build time.

## How to View the Site

### Option 1: View Online (GitHub)
You can browse the source code and raw content file directly on [GitHub](https://github.com/bikepaths/bikepaths).

### Option 2: Run Locally
1. **Install Dependencies**:
   ```bash
   npm install
   ```
2. **Start Development Server**:
   ```bash
   npm run dev
   ```
3. **View**: Open `http://localhost:3000` in your browser.

### Option 3: Deploy to the Web (Recommended)
Because this is a Next.js application, he easiest way to view the live site is to deploy it to **Vercel**:

1. Go to [Vercel.com](https://vercel.com) and sign up with GitHub.
2. Click "Add New Project" -> "Import" and select the `bikepaths` repository.
3. Click "Deploy".
4. Your site will be live at `https://bikepaths.vercel.app` (or similar).

Every time you sync content from your remote server to GitHub (using our system script), Vercel will automatically rebuild the site with the new content.

## Maintenance

- **Content Sync**: Run `./scripts/sync_bikepaths_blog.sh` from your workspace to Update content from Remote -> Local -> GitHub.
- **Clean Up**: Run `rm -rf node_modules .next` to save space when not developing locally.
