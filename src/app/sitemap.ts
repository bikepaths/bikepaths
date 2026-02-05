import { getAllPosts } from '@/lib/posts';
import { MetadataRoute } from 'next';

export default function sitemap(): MetadataRoute.Sitemap {
    const posts = getAllPosts();
    const baseUrl = 'https://bikepaths.vercel.app'; // Or your custom domain

    const postEntries = posts.map((post) => ({
        url: `${baseUrl}/${post.category}/${post.slug}`,
        lastModified: post.date ? new Date(post.date) : new Date(),
        changeFrequency: 'monthly' as const,
        priority: 0.7,
    }));

    const categories = Array.from(new Set(posts.map((post) => post.category)));
    const categoryEntries = categories.map((category) => ({
        url: `${baseUrl}/${category}`,
        lastModified: new Date(),
        changeFrequency: 'weekly' as const,
        priority: 0.8,
    }));

    return [
        {
            url: baseUrl,
            lastModified: new Date(),
            changeFrequency: 'daily',
            priority: 1,
        },
        ...categoryEntries,
        ...postEntries,
    ];
}
