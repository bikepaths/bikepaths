import fs from 'fs';
import path from 'path';
import matter from 'gray-matter'; // We might not need this if we parse manually, but good to have if standard frontmatter appears.

const contentDirectory = path.join(process.cwd(), 'blog');

export interface Post {
    slug: string;
    title: string;
    description: string;
    date: string;
    category: string;
    tags: string[];
    image?: string;
    content: string;
    type: string; // post, image, link
}

function parseCustomFrontmatter(fileContent: string) {
    const titleMatch = fileContent.match(/<!--t (.*?) t-->/);
    const descMatch = fileContent.match(/<!--d (.*?) d-->/);
    const tagMatch = fileContent.match(/<!--tag (.*?) tag-->/);
    const imageMatch = fileContent.match(/<!--image (.*?) image-->/);

    // Remove the comment lines from content
    const content = fileContent
        .replace(/<!--t .*? t-->/g, '')
        .replace(/<!--d .*? d-->/g, '')
        .replace(/<!--tag .*? tag-->/g, '')
        .replace(/<!--image .*? image-->/g, '')
        .trim();

    return {
        title: titleMatch ? titleMatch[1].trim() : '',
        description: descMatch ? descMatch[1].trim() : '',
        tags: tagMatch ? tagMatch[1].split(',').map(t => t.trim()) : [],
        image: imageMatch ? imageMatch[1].trim() : undefined,
        content,
    };
}

export function getAllPosts(): Post[] {
    // Recursively find all markdown files
    const posts: Post[] = [];

    function traverse(dir: string, category: string | null, type: string | null) {
        if (!fs.existsSync(dir)) return;

        const files = fs.readdirSync(dir);
        for (const file of files) {
            const fullPath = path.join(dir, file);
            const stat = fs.statSync(fullPath);

            if (stat.isDirectory()) {
                // Determine category and type based on depth
                // root/blog/[category]/[type]/file.md
                // dir starts at root/blog.
                const relativePath = path.relative(contentDirectory, fullPath);
                const parts = relativePath.split(path.sep);

                if (parts.length === 1) {
                    // We are in blog/[category]
                    traverse(fullPath, parts[0], null);
                } else if (parts.length === 2) {
                    // We are in blog/[category]/[type]
                    traverse(fullPath, parts[0], parts[1]);
                } else {
                    // Deeper? Just recurse, keeping cat/type
                    traverse(fullPath, category || parts[0], type || parts[1]);
                }
            } else if (file.endsWith('.md')) {
                // Parse File
                if (!category) continue; // Should have a category

                // Parse filename: DATE_TAGS_SLUG.md or similar
                // Example: 2026-01-30-12-00-00_society,relationships..._beyond-drama-triangle.md
                // We only really need the date and slug from the filename if not in frontmatter.

                // Regex to extract date
                const dateMatch = file.match(/^(\d{4}-\d{2}-\d{2})/);
                const date = dateMatch ? dateMatch[1] : '';

                // Slug extraction: remove date and extension, maybe tags?
                // Let's assume the slug is the part after the last underscore if underscores are used as separators
                // OR the user might want a clean slug from the filename. 
                // Example: 2026..._tags_slug.md. 
                // Let's try to extract the last part.
                let slug = file.replace('.md', '');
                if (slug.includes('_')) {
                    const parts = slug.split('_');
                    slug = parts[parts.length - 1];
                }

                const fileContent = fs.readFileSync(fullPath, 'utf8');
                const metadata = parseCustomFrontmatter(fileContent);

                posts.push({
                    slug,
                    title: metadata.title || slug,
                    description: metadata.description,
                    date,
                    category,
                    tags: metadata.tags,
                    image: metadata.image,
                    content: metadata.content,
                    type: type || 'post'
                });
            }
        }
    }

    traverse(contentDirectory, null, null);

    // Sort by date desc
    return posts.sort((a, b) => (a.date < b.date ? 1 : -1));
}

export function getPostBySlug(category: string, slug: string): Post | undefined {
    const posts = getAllPosts();
    return posts.find(p => p.slug === slug && p.category === category);
}
