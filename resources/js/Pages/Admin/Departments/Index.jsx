import { usePage } from '@inertiajs/react';
import AdminLayout from '../../../Components/AdminLayout';

function Status({ status }) {
    return status === 1
        ? <span className="mc-pill p-active"><i />Active</span>
        : <span className="mc-pill p-inactive"><i />Inactive</span>;
}

export default function DepartmentsIndex({ departments, routes }) {
    const { csrfToken } = usePage().props;

    return (
        <AdminLayout title="Departments" active="departments" routes={routes}>
            <div className="mc-head">
                <div><p className="mc-kicker">MediCare · Structure</p><h1 className="mc-title">Depart<em>ments</em></h1><p className="mc-sub">Clinical units, their load, and availability.</p></div>
                <div className="mc-head-acts"><a href={routes.create} className="mc-btn"><i aria-hidden="true" className="bi bi-plus-lg" /> Add department</a></div>
            </div>

            <div className="mc-ecg"><span>Live register</span><span>{departments.length} records</span></div>
            <section className="mc-card" aria-label="Department register">
                <div className="table-responsive">
                    <table className="mc-tbl">
                        <thead><tr><th>#</th><th>Department</th><th>Description</th><th>Status</th><th className="text-right">Actions</th></tr></thead>
                        <tbody>{departments.length ? departments.map((department, index) => (
                            <tr key={department.id}>
                                <td className="mc-idx">{String(index + 1).padStart(2, '0')}</td>
                                <td><div className="mc-who"><span className="mc-av b">{department.name.split(/\s+/).slice(0, 2).map((part) => part[0]).join('').toUpperCase()}</span><span><b>{department.name}</b></span></div></td>
                                <td>{department.description ? `${department.description.slice(0, 60)}${department.description.length > 60 ? '…' : ''}` : '–'}</td>
                                <td><Status status={department.status} /></td>
                                <td><div className="mc-acts">
                                    <a href={`${routes.editBase}/${department.id}/edit`} className="mc-btn sm dark"><i aria-hidden="true" className="bi bi-pencil" /> Edit</a>
                                    <form action={`${routes.deleteBase}/${department.id}`} method="post" className="m-0"
                                        onSubmit={(event) => { if (!window.confirm('Are you sure?')) event.preventDefault(); }}>
                                        <input type="hidden" name="_token" value={csrfToken} /><input type="hidden" name="_method" value="DELETE" />
                                        <button type="submit" className="mc-btn sm danger-ghost"><i aria-hidden="true" className="bi bi-trash" /> Delete</button>
                                    </form>
                                </div></td>
                            </tr>
                        )) : <tr><td colSpan="5"><div className="mc-empty"><b>Nothing on this chart</b>No departments found. Add the first unit to structure the clinic.</div></td></tr>}</tbody>
                    </table>
                </div>
            </section>
        </AdminLayout>
    );
}
