import { useForm } from '@inertiajs/react';
import FrontendLayout, { publicImage } from '../../../Components/FrontendLayout';
import { PageHeading } from '../../../Components/PublicPage';

export default function BlogShow({ blog, comments = [], recentPosts = [], categories = [], tags = [] }) {
    const form = useForm({ name: '', email: '', comment: '' });

    function submit(event) {
        event.preventDefault();
        form.post(`/blog/${blog.id}/comment`);
    }

    return (
        <FrontendLayout title={blog.title} seo={{ title: blog.title, description: blog.excerpt }}>
            <PageHeading title="From the MediCare journal" description="Ideas and information to help you make confident decisions about your health." />
            <article className="mx-auto grid max-w-7xl gap-12 px-5 py-14 sm:px-8 sm:py-18 lg:grid-cols-[minmax(0,1fr)_300px]">
                <div>
                    {blog.image && <img src={publicImage(blog.image)} alt={blog.title} className="max-h-[520px] w-full rounded-2xl object-cover" />}
                    <div className="mt-7 flex flex-wrap gap-x-5 gap-y-2 text-xs font-semibold text-muted">
                        {blog.category && <span className="text-teal">{blog.category}</span>}
                        <span>{blog.date}</span>
                        {blog.author && <span>By {blog.author}</span>}
                    </div>
                    <h1 className="mt-4 font-display text-4xl font-medium leading-tight text-ink sm:text-5xl">{blog.title}</h1>
                    {blog.excerpt && <p className="mt-5 border-l-2 border-gold pl-5 font-display text-xl leading-8 text-muted">{blog.excerpt}</p>}
                    <div className="blog-content mt-7 text-base leading-8 text-ink/80" dangerouslySetInnerHTML={{ __html: blog.content }} />
                    <div className="mt-8 flex flex-wrap items-center gap-3 border-y border-line py-5">
                        <span className="mr-2 text-xs font-bold uppercase tracking-wider text-muted">Share</span>
                        {[
                            ['Facebook', `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(window.location.href)}`],
                            ['X', `https://twitter.com/intent/tweet?url=${encodeURIComponent(window.location.href)}&text=${encodeURIComponent(blog.title)}`],
                            ['LinkedIn', `https://www.linkedin.com/sharing/share-offsite/?url=${encodeURIComponent(window.location.href)}`],
                        ].map(([label, href]) => <a key={label} href={href} target="_blank" rel="noreferrer" className="rounded-full border border-line px-4 py-2 text-xs font-semibold text-ink no-underline hover:border-teal hover:text-teal">{label}</a>)}
                    </div>

                    <section className="mt-10">
                        <h2 className="font-display text-2xl">{comments.length} comments</h2>
                        <div className="mt-5 space-y-4">
                            {comments.length ? comments.map((comment, index) => <article key={`${comment.date}-${index}`} className="rounded-xl bg-white p-5"><div className="flex flex-wrap justify-between gap-2"><strong className="text-sm">{comment.name}</strong><time className="text-xs text-muted">{comment.date}</time></div><p className="mb-0 mt-3 text-sm leading-7 text-muted">{comment.comment}</p></article>) : <p className="text-sm text-muted">No comments yet. Share your perspective.</p>}
                        </div>
                    </section>

                    <form onSubmit={submit} className="mt-9 rounded-2xl border border-line bg-white p-6 sm:p-8">
                        <h2 className="font-display text-2xl">Leave a comment</h2>
                        <p className="mt-2 text-sm text-muted">Your email address will not be published.</p>
                        {form.wasSuccessful && <p role="status" className="mt-4 rounded-lg bg-teal-pale p-3 text-sm text-teal-dark">Thank you. Your comment has been submitted for review.</p>}
                        {form.hasErrors && <p role="alert" className="mt-4 rounded-lg bg-red-50 p-3 text-sm text-red-800">Please correct the highlighted fields.</p>}
                        <div className="mt-5 grid gap-4 sm:grid-cols-2">
                            {[
                                ['name', 'Your name', 'text'],
                                ['email', 'Your email', 'email'],
                            ].map(([name, label, type]) => (
                                <div key={name}>
                                    <label htmlFor={`comment-${name}`} className="mb-2 block text-sm font-semibold">{label}</label>
                                    <input id={`comment-${name}`} type={type} className="w-full rounded-xl border border-line px-4 py-3 text-sm focus:border-teal focus:outline-none" value={form.data[name]} onChange={(event) => form.setData(name, event.target.value)} required />
                                    {form.errors[name] && <p role="alert" className="mt-1 text-xs text-red-700">{form.errors[name]}</p>}
                                </div>
                            ))}
                            <div className="sm:col-span-2">
                                <label htmlFor="comment-text" className="mb-2 block text-sm font-semibold">Comment</label>
                                <textarea id="comment-text" rows="5" className="w-full rounded-xl border border-line px-4 py-3 text-sm focus:border-teal focus:outline-none" value={form.data.comment} onChange={(event) => form.setData('comment', event.target.value)} required />
                                {form.errors.comment && <p role="alert" className="mt-1 text-xs text-red-700">{form.errors.comment}</p>}
                            </div>
                        </div>
                        <button type="submit" disabled={form.processing} className="mt-5 rounded-full bg-teal px-6 py-3 text-sm font-bold text-white hover:bg-teal-dark disabled:opacity-60">{form.processing ? 'Submitting…' : 'Submit comment'}</button>
                    </form>
                </div>

                <aside className="space-y-7">
                    {recentPosts.length > 0 && <section className="rounded-2xl border border-line bg-white p-6"><h2 className="font-display text-xl">Recent articles</h2><ul className="mt-4 space-y-4">{recentPosts.map((post) => <li key={post.slug} className="flex gap-3">{post.image && <img src={publicImage(post.image)} alt="" className="h-16 w-16 shrink-0 rounded-lg object-cover" />}<div><a href={`/blog/${post.slug}`} className="text-sm font-semibold text-ink no-underline hover:text-teal">{post.title}</a><time className="mt-1 block text-xs text-muted">{post.date}</time></div></li>)}</ul></section>}
                    {categories.length > 0 && <section className="rounded-2xl border border-line bg-white p-6"><h2 className="font-display text-xl">Categories</h2><ul className="mt-4 space-y-3">{categories.map((category) => <li key={category.name}><a href={`/blog?category=${encodeURIComponent(category.name)}`} className="text-sm text-muted no-underline hover:text-teal">{category.name} ({category.count})</a></li>)}</ul></section>}
                    {tags.length > 0 && <section className="rounded-2xl border border-line bg-white p-6"><h2 className="font-display text-xl">Topics</h2><div className="mt-4 flex flex-wrap gap-2">{tags.map((tag) => <a key={tag.name} href={`/blog?tag=${encodeURIComponent(tag.name)}`} className="rounded-full border border-line px-3 py-1.5 text-xs text-muted no-underline hover:border-teal hover:text-teal">{tag.name}</a>)}</div></section>}
                </aside>
            </article>
        </FrontendLayout>
    );
}
