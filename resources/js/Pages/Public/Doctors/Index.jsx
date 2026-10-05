import { useState } from 'react';
import { router } from '@inertiajs/react';
import FrontendLayout from '../../../Components/FrontendLayout';
import { DoctorCards, PageHeading, Pagination } from '../../../Components/PublicPage';

export default function DoctorsIndex({
    doctors,
    departments = [],
    filters = {},
    searchEnabled,
    pageTitle,
    noDoctorsMessage,
    seo,
}) {
    const [query, setQuery] = useState({
        search: filters.search || '',
        department: filters.department || '',
        date: filters.date || '',
    });

    function submit(event) {
        event.preventDefault();
        router.get('/doctor', query, { preserveState: true, preserveScroll: true, replace: true });
    }

    return (
        <FrontendLayout title={seo?.title || pageTitle || 'Our doctors'} seo={seo}>
            <PageHeading title={pageTitle || 'Our doctors'} description="Meet experienced specialists ready to listen, guide, and care for you." />
            <section className="mx-auto max-w-7xl px-5 py-14 sm:px-8 sm:py-18">
                {searchEnabled && (
                    <form onSubmit={submit} className="mb-10 grid gap-3 rounded-2xl border border-line bg-white p-5 sm:grid-cols-2 lg:grid-cols-[1.5fr_1fr_1fr_auto]">
                        <div>
                            <label htmlFor="doctor-search" className="mb-1.5 block text-xs font-bold uppercase tracking-wider text-muted">Search specialists</label>
                            <input id="doctor-search" name="search" value={query.search} onChange={(event) => setQuery({ ...query, search: event.target.value })}
                                className="w-full rounded-xl border border-line px-4 py-3 text-sm focus:border-teal focus:outline-none" placeholder="Name, specialty or department" />
                        </div>
                        <div>
                            <label htmlFor="doctor-department" className="mb-1.5 block text-xs font-bold uppercase tracking-wider text-muted">Department</label>
                            <select id="doctor-department" name="department" value={query.department} onChange={(event) => setQuery({ ...query, department: event.target.value })}
                                className="w-full rounded-xl border border-line bg-white px-4 py-3 text-sm focus:border-teal focus:outline-none">
                                <option value="">All departments</option>
                                {departments.map((department) => <option key={department} value={department}>{department}</option>)}
                            </select>
                        </div>
                        <div>
                            <label htmlFor="doctor-date" className="mb-1.5 block text-xs font-bold uppercase tracking-wider text-muted">Available on</label>
                            <input id="doctor-date" name="date" type="date" min={new Date().toLocaleDateString('en-CA')} value={query.date}
                                onChange={(event) => setQuery({ ...query, date: event.target.value })}
                                className="w-full rounded-xl border border-line px-4 py-3 text-sm focus:border-teal focus:outline-none" />
                        </div>
                        <button type="submit" className="self-end rounded-full bg-teal px-6 py-3 text-sm font-bold text-white hover:bg-teal-dark">Find a doctor</button>
                    </form>
                )}
                {doctors.data.length
                    ? <DoctorCards doctors={doctors.data} />
                    : <p className="rounded-2xl border border-line bg-white p-10 text-center text-muted">{noDoctorsMessage || 'No doctors found.'}</p>}
                <Pagination pagination={doctors} />
            </section>
        </FrontendLayout>
    );
}
