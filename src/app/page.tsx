import { getAllPosts } from '@/lib/posts';
import Link from 'next/link';
import { format } from 'date-fns';

export default async function Home({
  searchParams,
}: {
  searchParams: Promise<{ page?: string }>;
}) {
  const { page } = (await searchParams) || {};
  const currentPage = Math.max(1, parseInt(page || '1', 10) || 1);
  const pageSize = 24;

  const allPosts = getAllPosts();
  const totalPages = Math.ceil(allPosts.length / pageSize);
  const validPage = Math.min(currentPage, totalPages || 1);

  const startIndex = (validPage - 1) * pageSize;
  const posts = allPosts.slice(startIndex, startIndex + pageSize);

  return (
    <div>
      <section className="hero">
        <h1>bikepaths.org</h1>
        <p><strong>Pre-publishing Review Site</strong></p>
        <p style={{ fontSize: '0.9rem', marginTop: '1rem', opacity: 0.5 }}>Synced from <a href="https://bikepaths.org/blog/">165.232.151.110</a></p>
      </section>

      <div className="grid">
        {posts.map((post) => (
          <Link href={`/${post.category}/${post.slug}`} key={`${post.category}-${post.slug}`} className="card">
            <div className="card-image-wrap">
              {post.image ? (
                <img
                  src={post.image}
                  alt={post.title}
                  loading="lazy"
                  decoding="async"
                  className="card-image-img"
                />
              ) : (
                <div className="card-image-placeholder" />
              )}
            </div>
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

      {totalPages > 1 && (
        <div className="pagination">
          {validPage > 1 ? (
            <Link
              href={validPage === 2 ? '/' : `/?page=${validPage - 1}`}
              className="pagination-btn"
            >
              &larr; Previous
            </Link>
          ) : (
            <span className="pagination-btn disabled">&larr; Previous</span>
          )}

          <span className="pagination-info">
            Page {validPage} of {totalPages}
          </span>

          {validPage < totalPages ? (
            <Link href={`/?page=${validPage + 1}`} className="pagination-btn">
              Next &rarr;
            </Link>
          ) : (
            <span className="pagination-btn disabled">Next &rarr;</span>
          )}
        </div>
      )}
    </div>
  );
}
