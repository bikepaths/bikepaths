import { getAllPosts, getPostBySlug } from '@/lib/posts';
import { notFound } from 'next/navigation';
import { remark } from 'remark';
import html from 'remark-html';
import { format } from 'date-fns';
import Link from 'next/link';

export function generateStaticParams() {
    const posts = getAllPosts();
    return posts.map((post) => ({
        category: post.category,
        slug: post.slug,
    }));
}

// Define params type as a Promise
export default async function PostPage({ params }: { params: Promise<{ category: string; slug: string }> }) {
    const { category, slug } = await params;
    const post = getPostBySlug(category, slug);

    if (!post) {
        notFound();
    }

    const processedContent = await remark()
        .use(html)
        .process(post.content);
    const contentHtml = processedContent.toString();

    return (
        <article>
            <Link href={`/${category}`} className="back-link">← Back to {category}</Link>

            <header style={{ borderBottom: 'none', marginBottom: '1rem', paddingBottom: 0, justifyContent: 'flex-start', flexDirection: 'column', alignItems: 'flex-start' }}>
                <div style={{ color: 'var(--primary)', textTransform: 'uppercase', fontSize: '0.9rem', marginBottom: '0.5rem', fontWeight: 'bold' }}>
                    {category}
                </div>
                <h1>{post.title}</h1>
                {post.description && <p style={{ fontSize: '1.2rem', opacity: 0.8, fontStyle: 'italic' }}>{post.description}</p>}
                <div style={{ marginTop: '1rem', opacity: 0.6, fontSize: '0.9rem' }}>
                    {post.date ? format(new Date(post.date), 'MMMM d, yyyy') : ''}
                </div>
            </header>

            {post.image && (
                <img src={post.image} alt={post.title} />
            )}

            <div className="prose" dangerouslySetInnerHTML={{ __html: contentHtml }} />
        </article>
    );
}
