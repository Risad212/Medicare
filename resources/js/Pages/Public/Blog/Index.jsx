import { router } from '@inertiajs/react';
import FrontendLayout from '../../../Components/FrontendLayout';
import { BlogCards, PageHeading, Pagination } from '../../../Components/PublicPage';

export default function BlogIndex({ blogs, categories = [], tags = [], filters = {}, seo }) {
    function filterBy(key, value) {
        router.get('/blog', { ...filters, [key]: value || undefined }, { preserveState: true, preserveScroll: true, replace: true });
    }

    return (
        <FrontendLayout title={seo?.title || 'Health journal'} seo={seo}>
            <PageHeading title="The MediCare journal" description="Practical perspectives and thoughtful guidance for healthier days." />
            <section className="mx-auto grid max-w-7xl gap-10 px-5 py-14 sm:px-8 sm:py-18 lg:grid-cols-[1fr_280px]">
                <div>
                    {filters.category && <p className="mb-6 text-sm text-muted">Category: <strong className="text-ink">{filters.category}</strong> <button onClick={() => filterBy('category', '')} className="ml-2 font-semibold text-teal underline">Clear</button></p>}
                    {filters.tag && <p className="mb-6 text-sm text-muted">Tag: <strong className="text-ink">{filters.tag}</strong> <button onClick={() => filterBy('tag', '')} className="ml-2 font-semibold text-teal underline">Clear</button></p>}
                    {blogs.data.length
                        ? <BlogCards blogs={blogs.data} />
                        : <p className="rounded-2xl border border-line bg-white p-10 text-center text-muted">No articles found for this selection.</p>}
                    <Pagination pagination={blogs} />
                </div>
                <aside className="space-y-8">
                    {categories.length > 0 && (
                        <section className="rounded-2xl border border-line bg-white p-6">
                            <h2 className="font-display text-xl">Categories</h2>
                            <ul className="mt-4 space-y-3">
                                {categories.map((category) => <li key={category.name}><button type="button" onClick={() => filterBy('category', category.name)} className={`flex w-full justify-between text-left text-sm ${filters.category === category.name ? 'font-bold text-teal' : 'text-muted hover:text-teal'}`}><span>{category.name}</span><span>{category.count}</span></button></li>)}
                            </ul>
                        </section>
                    )}
                    {tags.length > 0 && (
                        <section className="rounded-2xl border border-line bg-white p-6">
                            <h2 className="font-display text-xl">Explore topics</h2>
                            <div className="mt-4 flex flex-wrap gap-2">
                                {tags.map((tag) => <button key={tag.name} type="button" onClick={() => filterBy('tag', tag.name)} className={`rounded-full border px-3 py-1.5 text-xs ${filters.tag === tag.name ? 'border-teal bg-teal-pale text-teal-dark' : 'border-line text-muted hover:border-teal'}`}>{tag.name}</button>)}
                            </div>
                        </section>
                    )}
                </aside>
            </section>
        </FrontendLayout>
    );
}
