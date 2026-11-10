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
        .use(html, { sanitize: false })
        .process(post.content);
    const contentHtml = processedContent.toString();

    const postDate = post.date ? new Date(post.date + 'T06:00:00') : null;
    const now = new Date();
    const diffDays = postDate ? Math.ceil((postDate.getTime() - now.getTime()) / (1000 * 60 * 60 * 24)) : 0;
    const isScheduled = diffDays > 0;

    return (
        <article>
            <Link href={`/`} className="back-link">← Back to bikepaths Pre-Publish Preview Main Index</Link>

            <header style={{ borderBottom: 'none', marginBottom: '1rem', paddingBottom: 0, justifyContent: 'flex-start', flexDirection: 'column', alignItems: 'flex-start' }}>
                <div style={{ color: 'var(--primary)', textTransform: 'uppercase', fontSize: '0.9rem', marginBottom: '0.5rem', fontWeight: 'bold' }}>
                    {category}
                </div>
                <h1>{post.title}</h1>
                {post.description && <p style={{ fontSize: '1.2rem', opacity: 0.8, fontStyle: 'italic' }}>{post.description}</p>}
                
                {isScheduled && postDate ? (
                    <div className="schedule-badge">
                        <span className="badge-icon" style={{ fontSize: '1.25rem' }}>⏳</span>
                        <div>
                            <div><strong>Pre-Publish Preview</strong> &bull; Scheduled for release in <strong>{diffDays} {diffDays === 1 ? 'day' : 'days'}</strong></div>
                            <div style={{ fontSize: '0.85rem', opacity: 0.85, marginTop: '0.2rem' }}>Target Date: {format(postDate, 'EEEE, MMMM d, yyyy')} at 6:00 AM</div>
                        </div>
                    </div>
                ) : (
                    <div style={{ marginTop: '1rem', opacity: 0.6, fontSize: '0.9rem' }}>
                        {postDate ? format(postDate, 'MMMM d, yyyy') : ''}
                    </div>
                )}
            </header>

            {post.image && (
                <img src={post.image} alt={post.title} />
            )}

            <div className="prose" dangerouslySetInnerHTML={{ __html: contentHtml }} />

            <div style={{ marginTop: '3rem', paddingTop: '2rem', borderTop: '1px solid var(--card-border)' }}>
                <Link href={`/`} className="back-link">← Back to bikepaths Pre-Publish Preview Main Index</Link>
            </div>
        </article>
    );
}
