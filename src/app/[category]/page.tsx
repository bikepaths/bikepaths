import { getAllPosts } from '@/lib/posts';
import Link from 'next/link';
import { format } from 'date-fns';
import { notFound } from 'next/navigation';

export function generateStaticParams() {
    const posts = getAllPosts();
    const categories = Array.from(new Set(posts.map((post) => post.category)));
    return categories.map((category) => ({
        category,
    }));
}

// Define params type as a Promise
export default async function CategoryPage({ params }: { params: Promise<{ category: string }> }) {
    const { category } = await params;
    const posts = getAllPosts().filter((p) => p.category === category);

    if (posts.length === 0) {
        // If no posts, maybe it's not a valid category, or just empty.
        // We can show an empty state or 404.
        // Since we generate params based on existing posts, this should hit.
        // But if user types random url...
        return notFound();
    }

    return (
        <div>
            <section className="hero">
                <h1 style={{ textTransform: 'capitalize' }}>{category}</h1>
                <p>Archive of {category}.</p>
            </section>

            <div className="grid">
                {posts.map((post) => (
                    <Link href={`/${post.category}/${post.slug}`} key={post.slug} className="card">
                        {post.image ? (
                            <div className="card-image" style={{ backgroundImage: `url(${post.image})` }} />
                        ) : (
                            <div className="card-image" style={{ background: `linear-gradient(45deg, #111, #222)` }} />
                        )}
                        <div className="card-content">
                            <div className="card-meta">{post.type}</div>
                            <h2>{post.title}</h2>
                            <p>{post.description}</p>
                            <div className="card-footer">
                                {post.date ? format(new Date(post.date), 'MMMM d, yyyy') : ''}
                            </div>
                        </div>
                    </Link>
                ))}
            </div>
        </div>
    );
}
