import { router } from '@inertiajs/react';
import AdminLayout from '../../../Components/AdminLayout';

export default function LanguagesIndex({ languages, labels, routes }) {
    return (
        <AdminLayout title={labels.list} active="languages" routes={routes}>
            <div className="mc-head">
                <div><p className="mc-kicker">MediCare · Settings</p><h1 className="mc-title">{labels.list}</h1><p className="mc-sub">{labels.listSub}</p></div>
                <div className="mc-head-acts"><a href={routes.create} className="mc-btn"><i aria-hidden="true" className="bi bi-plus-lg" /> {labels.add}</a></div>
            </div>
            <section className="mc-card"><div className="overflow-x-auto"><table className="mc-tbl">
                <thead><tr><th>#</th><th>{labels.name}</th><th>{labels.code}</th><th>{labels.status}</th><th className="text-right">{labels.actions}</th></tr></thead>
                <tbody>{languages.data.length ? languages.data.map((language, index) => <tr key={language.id}>
                    <td className="mc-idx">{String((languages.currentPage - 1) * 20 + index + 1).padStart(2, '0')}</td>
                    <td>{language.name}{language.isDefault && <span className="ml-2 rounded-full bg-teal-bg px-2 py-0.5 text-xs font-bold text-teal-dk">{labels.default}</span>}</td>
                    <td><code>{language.code}</code></td>
                    <td><span className={`mc-pill ${language.isActive ? 'p-confirmed' : 'p-cancelled'}`}>{language.isActive ? labels.active : labels.inactive}</span></td>
                    <td><div className="mc-acts justify-end">
                        <a href={language.routes.edit} className="mc-link">{labels.edit}</a>
                        {!language.isDefault && <>
                            <button type="button" className="mc-link" onClick={() => router.post(language.routes.default)}>{labels.setDefault}</button>
                            <button type="button" className="mc-link" onClick={() => router.post(language.routes.toggle)}>{language.isActive ? labels.deactivate : labels.activate}</button>
                            <button type="button" className="mc-link text-red-t" onClick={() => { if (window.confirm(labels.deleteConfirm)) router.delete(language.routes.delete); }}>{labels.delete}</button>
                        </>}
                    </div></td>
                </tr>) : <tr><td colSpan="5" className="py-8 text-center text-mut">{labels.noLanguages}</td></tr>}</tbody>
            </table></div>
            {languages.lastPage > 1 && <div className="mc-pg"><span>{languages.total}</span><nav aria-label="Language pages" className="flex items-center gap-1">{languages.pageUrls.map((page) => <a key={page.number} href={page.url} aria-current={page.number === languages.currentPage ? 'page' : undefined} className={`page-link ${page.number === languages.currentPage ? 'active' : ''}`}>{page.number}</a>)}</nav></div>}
            </section>
        </AdminLayout>
    );
}
