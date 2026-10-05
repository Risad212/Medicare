import { useForm } from '@inertiajs/react';
import AdminLayout from '../../../Components/AdminLayout';

const inputClass = 'w-full rounded-lg border border-line bg-white px-3 py-2.5 text-sm text-ink outline-none transition focus:border-teal focus:ring-2 focus:ring-teal/15';

function Field({ form, name, label, type = 'text', children, full = false, hint, ...props }) {
    const id = `blog-${name}`;
    const error = form.errors[name];

    return (
        <div className={`mc-f ${full ? 'full' : ''}`}>
            <label htmlFor={id}>{label}</label>
            {children || <input id={id} className={`${inputClass} ${error ? 'border-red-500' : ''}`} type={type}
                value={type === 'file' ? undefined : form.data[name] ?? ''} onChange={(event) => form.setData(name, type === 'file' ? event.target.files[0] : event.target.value)}
                aria-invalid={Boolean(error)} aria-describedby={error ? `${id}-error` : hint ? `${id}-hint` : undefined} {...props} />}
            {error && <p id={`${id}-error`} role="alert" className="mt-1 text-xs text-red-t">{error}</p>}
            {!error && hint && <span id={`${id}-hint`} className="mc-hint">{hint}</span>}
        </div>
    );
}

export default function BlogForm({ mode, blog = null, categories = [], tags = [], routes, storageUrl }) {
    const isEdit = mode === 'edit';
    const form = useForm({
        title: blog?.title ?? '',
        excerpt: blog?.excerpt ?? '',
        content: blog?.content ?? '',
        image: null,
        category: blog?.category ?? '',
        tags: blog?.tags ?? '',
        status: blog ? blog.status : true,
    });
    const imageUrl = blog?.image ? `${storageUrl}/${blog.image.replace(/^\/+/, '')}` : null;

    function submit(event) {
        event.preventDefault();
        form.transform(({ status, ...data }) => ({
            ...(status ? { ...data, status: '1' } : data),
            ...(isEdit ? { _method: 'PUT' } : {}),
        })).post(isEdit ? routes.update : routes.store);
    }

    return (
        <AdminLayout title={isEdit ? 'Edit blog post' : 'New blog post'} active="blogs" routes={routes}>
            <div className="mc-head"><div><p className="mc-kicker">MediCare · Publishing</p><h1 className="mc-title">{isEdit ? <>Edit <em>post</em></> : <>New <em>post</em></>}</h1>
                <p className="mc-sub">{isEdit ? (blog.status ? 'Published · live on the site' : 'Draft · private for now') : 'Patient education. Drafts stay private until published.'}</p></div></div>
            <form onSubmit={submit} encType="multipart/form-data" noValidate>
                <div className="mc-grid">
                    <aside className="mc-side">
                        {imageUrl ? <div className="mc-avbig"><img src={imageUrl} alt="" className="h-full w-full rounded-full object-cover" /></div> : <div className="mc-avbig">BL</div>}
                        <div className="k">{isEdit ? 'Currently editing' : 'New record'}</div><h2>{blog?.title || 'Untitled post'}</h2>
                        <p>{isEdit ? `${blog.category || 'Uncategorized'} · ${blog.date}` : 'Draft · not published'}</p>
                        <ul className="mc-steps"><li><span className="n">1</span>Content</li><li><span className="n">2</span>Filing</li></ul>
                    </aside>
                    <div>
                        {form.hasErrors && <div role="alert" className="mb-4 rounded-lg bg-red-bg px-4 py-3 text-sm text-red-t">Please review the highlighted fields and try again.</div>}
                        <section className="mc-sec">
                            <div className="mc-sec-hd"><span className="no">01</span><h3>Content</h3><p>The article itself.</p></div>
                            <div className="mc-sec-bd">
                                <Field form={form} name="title" label="Title" full required placeholder="e.g. 5 Early Signs of Heart Disease" />
                                <Field form={form} name="excerpt" label="Excerpt (short description)" full hint="Up to 500 characters.">
                                    <textarea id="blog-excerpt" className={inputClass} rows="2" value={form.data.excerpt} onChange={(event) => form.setData('excerpt', event.target.value)} />
                                </Field>
                                <Field form={form} name="content" label="Content" full hint="HTML is sanitized by Laravel before saving.">
                                    <textarea id="blog-content" className={`${inputClass} font-mono text-xs`} rows="12" value={form.data.content} onChange={(event) => form.setData('content', event.target.value)} required />
                                </Field>
                                <Field form={form} name="image" label="Image" type="file" full accept="image/jpeg,image/png,image/webp" hint="JPG · PNG · WEBP · max 2 MB." />
                            </div>
                        </section>
                        <section className="mc-sec">
                            <div className="mc-sec-hd"><span className="no">02</span><h3>Filing</h3><p>How it is found — and whether it is live.</p></div>
                            <div className="mc-sec-bd">
                                <Field form={form} name="category" label="Category">
                                    <select id="blog-category" className={inputClass} value={form.data.category} onChange={(event) => form.setData('category', event.target.value)}>
                                        <option value="">Select category</option>{categories.map((item) => <option key={item.name} value={item.name}>{item.name}</option>)}
                                    </select>
                                </Field>
                                <Field form={form} name="tags" label="Tag">
                                    <select id="blog-tags" className={inputClass} value={form.data.tags} onChange={(event) => form.setData('tags', event.target.value)}>
                                        <option value="">Select tag</option>{tags.map((item) => <option key={item.name} value={item.name}>{item.name}</option>)}
                                    </select>
                                </Field>
                                <div className="mc-f"><span className="mb-2 block text-sm font-semibold">Publication status</span>
                                    <label className="mc-check"><input type="checkbox" checked={form.data.status} onChange={(event) => form.setData('status', event.target.checked)} /> Published</label>
                                </div>
                            </div>
                        </section>
                        <div className="mc-formacts">
                            <button type="submit" className="mc-btn" disabled={form.processing}>{form.processing ? 'Saving…' : isEdit ? 'Update blog post' : 'Save blog post'}</button>
                            <a href={routes.index} className="mc-btn ghost">Cancel</a>
                        </div>
                    </div>
                </div>
            </form>
        </AdminLayout>
    );
}
