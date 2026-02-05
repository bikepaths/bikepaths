import { getAllPosts } from '@/lib/posts';
import Link from 'next/link';
import { format } from 'date-fns';

export default function Home() {
  const posts = getAllPosts();

  return (
    <div>
      <section className="hero">
        <h1>Bikepaths</h1>
        <p>A hybrid digital garden exploring the intersections of <strong>Society</strong>, <strong>Technology</strong>, and the <strong>Human Mind</strong>.</p>
        <p style={{ fontSize: '0.9rem', marginTop: '1rem', opacity: 0.5 }}>Source of Truth: synced from 165.232.151.110</p>
      </section>

      <div className="grid">
        {posts.map((post) => (
          <Link href={`/${post.category}/${post.slug}`} key={`${post.category}-${post.slug}`} className="card">
            {post.image ? (
              <div className="card-image" style={{ backgroundImage: `url(${post.image})` }} />
            ) : (
              <div className="card-image" style={{ background: `linear-gradient(45deg, #111, #222)` }} />
            )}
            <div className="card-content">
              <div className="card-meta">{post.category}</div>
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
