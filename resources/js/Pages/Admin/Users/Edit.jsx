import { useForm, usePage } from '@inertiajs/react';
import AdminLayout from '../../../Components/AdminLayout';

export default function UserEdit({ user, staffRoles, routes }) {
    const { flash = {} } = usePage().props;
    const form = useForm({ staff_role: user.role });

    function submit(event) {
        event.preventDefault();
        form.put(routes.update, { preserveScroll: true });
    }

    return (
        <AdminLayout title="User access" active="users" routes={routes}>
            <div className="mc-head">
                <div>
                    <p className="mc-kicker">MediCare · Access control</p>
                    <h1 className="mc-title">Access for <em>{user.name}</em></h1>
                    <p className="mc-sub">{user.email} · joined {user.joinedAt || '—'}</p>
                </div>
            </div>

            {flash.error && <div role="alert" className="mb-4 rounded-lg bg-red-bg px-4 py-3 text-sm text-red-t">{flash.error}</div>}
            <form onSubmit={submit}>
                <section className="mc-sec">
                    <div className="mc-sec-hd"><span className="no">01</span><h3>Staff role</h3><p>Controls login landing page and panel access.</p></div>
                    <div className="mc-sec-bd">
                        <div className="mc-f full">
                            <label htmlFor="staff-role">Primary role <i className="req">*</i></label>
                            <select
                                id="staff-role"
                                name="staff_role"
                                className="w-full rounded-lg border border-line bg-white px-3 py-2.5 text-sm text-ink outline-none focus:border-teal focus:ring-2 focus:ring-teal/15"
                                value={form.data.staff_role}
                                onChange={(event) => form.setData('staff_role', event.target.value)}
                                aria-invalid={Boolean(form.errors.staff_role)}
                                aria-describedby={form.errors.staff_role ? 'staff-role-error' : 'staff-role-hint'}
                                required
                            >
                                {staffRoles.map((role) => <option key={role} value={role}>{role.replaceAll('-', ' ').replace(/\b\w/g, (character) => character.toUpperCase())}</option>)}
                            </select>
                            {form.errors.staff_role
                                ? <p id="staff-role-error" role="alert" className="mt-1 text-xs text-red-t">{form.errors.staff_role}</p>
                                : <span id="staff-role-hint" className="mc-hint">Changing a role changes which staff panel this account can access. Patient accounts remain protected by Laravel authorization.</span>}
                        </div>
                    </div>
                </section>
                <div className="mc-formacts">
                    <button type="submit" className="mc-btn" disabled={form.processing}>{form.processing ? 'Saving…' : 'Save access'}</button>
                    <a href={routes.index} className="mc-btn ghost">Cancel</a>
                </div>
            </form>
        </AdminLayout>
    );
}
