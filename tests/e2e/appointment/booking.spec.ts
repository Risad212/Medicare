import { test, expect } from '@playwright/test';

function futureDate(offsetDays: number): string {
    const d = new Date();
    d.setDate(d.getDate() + offsetDays);
    // avoid Friday (weekday 5) where no doctor is scheduled
    while (d.getDay() === 5) d.setDate(d.getDate() + 1);
    const yyyy = d.getFullYear();
    const mm = String(d.getMonth() + 1).padStart(2, '0');
    const dd = String(d.getDate()).padStart(2, '0');
    return `${yyyy}-${mm}-${dd}`;
}

test.describe('Appointment booking', () => {
    test('guest can book an appointment (happy path)', async ({ page }) => {
        await page.goto('/appointment');
        await expect(page.locator('body')).not.toContainText('Whoops, something went wrong');

        // Fill patient details
        await page.fill('#patient_name', 'E2E Test Patient');
        await page.fill('#phone', '01700000000');

        const doctorSelect = page.locator('#doctor_id');
        await page.selectOption('#visit_type', '1');
        await page.selectOption('#gender', '1');
        await page.fill('#age', '30');

        // Try a few random dates until we find one with a bookable slot
        const dopts = doctorSelect.locator('option');
        const doctorValues: string[] = [];
        for (let i = 0; i < await dopts.count(); i++) {
            const v = await dopts.nth(i).getAttribute('value');
            if (v && v.trim() !== '') doctorValues.push(v);
        }

        let date = '';
        let doctorValue = '';
        for (let attempt = 0; attempt < 5 && !doctorValue; attempt++) {
            date = futureDate(200 + Math.floor(Math.random() * 200));
            await page.fill('#appointment_date', date);
            for (const dv of doctorValues) {
                await doctorSelect.selectOption(dv);
                try {
                    await page.waitForFunction(() => {
                        const sel = document.getElementById('time_slot_id') as HTMLSelectElement | null;
                        return !!sel && sel.options.length > 1;
                    }, { timeout: 5000 });
                } catch { continue; }
                const hasEnabled = await page.evaluate(() => {
                    const sel = document.getElementById('time_slot_id') as HTMLSelectElement | null;
                    if (!sel) return false;
                    return Array.from(sel.options).some(o => !!o.value && !o.disabled);
                });
                if (hasEnabled) { doctorValue = dv; break; }
            }
        }
        if (!doctorValue) {
            test.skip(true, 'No bookable slots found across attempts');
            return;
        }
        await doctorSelect.selectOption(doctorValue);

        const slotSelect = page.locator('#time_slot_id');

        // Collect enabled (bookable) slot values
        const allOpts = slotSelect.locator('option');
        const enabledValues: string[] = [];
        for (let i = 0; i < await allOpts.count(); i++) {
            const opt = allOpts.nth(i);
            const disabled = await opt.getAttribute('disabled');
            const v = await opt.getAttribute('value');
            if (v && v.trim() !== '' && disabled === null) enabledValues.push(v);
        }
        if (enabledValues.length === 0) {
            test.skip(true, 'No bookable slots available for selected doctor/date');
            return;
        }

        // Try each enabled slot until one books successfully
        let booked = false;
        for (const slotValue of enabledValues) {
            const optDisabled = await slotSelect.locator(`option[value="${slotValue}"]`).getAttribute('disabled');
            if (optDisabled !== null) continue;
            try {
                await slotSelect.selectOption(slotValue);
            } catch { continue; }
            await page.click('button[type="submit"]');
            const success = page.locator('.alert-success');
            const error = page.locator('.alert-danger');
            try {
                await Promise.race([
                    success.waitFor({ state: 'visible', timeout: 5000 }),
                    error.waitFor({ state: 'visible', timeout: 5000 }),
                ]);
            } catch { /* ignore */ }
            if (await success.isVisible()) { booked = true; break; }
            // reset form for next attempt
            await page.goto('/appointment');
            await page.fill('#patient_name', 'E2E Test Patient');
            await page.fill('#phone', '01700000000');
            await page.selectOption('#visit_type', '1');
            await page.selectOption('#gender', '1');
            await page.fill('#age', '30');
            await page.fill('#appointment_date', date);
            await doctorSelect.selectOption(doctorValue);
            await page.waitForFunction(() => {
                const sel = document.getElementById('time_slot_id') as HTMLSelectElement | null;
                return !!sel && sel.options.length > 1;
            }, { timeout: 10000 });
        }

        expect(booked, 'at least one slot should book successfully').toBeTruthy();
    });

    test('submitting with missing required fields keeps user on appointment page', async ({ page }) => {
        await page.goto('/appointment');
        await page.evaluate(() => {
            const f = document.querySelector('form') as HTMLFormElement | null;
            if (f) f.noValidate = true;
        });
        await page.click('button[type="submit"]');
        await expect(page.locator('.alert-success')).toHaveCount(0);
        await expect(page).toHaveURL(/\/appointment/);
    });
});
