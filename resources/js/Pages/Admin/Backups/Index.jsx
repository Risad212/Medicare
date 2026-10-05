import { useForm } from '@inertiajs/react';
import AdminLayout from '../../../Components/AdminLayout';

export default function BackupsIndex({ backups, routes }) {
    const form = useForm();

    function runBackup(event) {
        event.preventDefault();
        if (window.confirm('Run a full backup now? This may take a minute.')) {
            form.post(routes.run);
        }
    }

    return (
        <AdminLayout title="Database backups" active="backups" routes={routes}>
            <div className="mc-head">
                <div><p className="mc-kicker">MediCare · System</p><h1 className="mc-title">Database <em>Backups</em></h1><p className="mc-sub">Full database + uploaded files, taken daily at 02:00. Old backups are cleaned up automatically.</p></div>
                <div className="mc-head-acts"><form onSubmit={runBackup}><button type="submit" className="mc-btn" disabled={form.processing}><i aria-hidden="true" className="bi bi-database-add" /> {form.processing ? 'Running backup…' : 'Run Backup Now'}</button></form></div>
            </div>
            {form.errors.backup && <div role="alert" className="mb-4 rounded-lg bg-red-bg px-4 py-3 text-sm text-red-t">{form.errors.backup}</div>}
            <section className="mc-card">
                <div className="border-b border-line px-4 py-3"><h2 className="m-0 text-sm font-bold">Backup history ({backups.length})</h2></div>
                <div className="overflow-x-auto"><table className="mc-tbl">
                    <thead><tr><th>File</th><th>Size</th><th>Created</th><th>Download</th></tr></thead>
                    <tbody>{backups.length ? backups.map((backup) => <tr key={backup.name}>
                        <td><b>{backup.name}</b></td><td className="mc-num">{(backup.size / 1048576).toFixed(2)} MB</td><td>{backup.createdAt}</td>
                        <td><a href={backup.downloadUrl} className="mc-btn sm"><i aria-hidden="true" className="bi bi-download" /> Download</a></td>
                    </tr>) : <tr><td colSpan="4"><div className="mc-empty"><b>No backups yet</b>Run your first backup with the button above — the daily schedule takes over from there.</div></td></tr>}</tbody>
                </table></div>
            </section>
        </AdminLayout>
    );
}
