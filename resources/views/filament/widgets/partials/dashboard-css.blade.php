<style>
    .dasbor-periode {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: .75rem;
        padding: .75rem;
        border: 1px solid var(--gray-200, #e5e7eb);
        border-radius: .75rem;
        background: var(--gray-50, #f9fafb);
        color: var(--gray-950, #111827);
    }

    .dasbor-periode__judul {
        margin: 0;
        font-size: .875rem;
        font-weight: 600;
    }

    .dasbor-periode__ket {
        margin: .125rem 0 0;
        font-size: .75rem;
        color: var(--gray-500, #6b7280);
    }

    .dasbor-periode__pilihan {
        display: flex;
        gap: .25rem;
    }

    .dasbor-periode__tautan {
        padding: .35rem .75rem;
        border-radius: .5rem;
        color: var(--gray-600, #4b5563);
        font-size: .875rem;
        font-weight: 500;
        text-decoration: none;
    }

    .dasbor-periode__tautan:hover {
        background: var(--gray-100, #f3f4f6);
    }

    .dasbor-periode__tautan.is-aktif {
        background: var(--primary-600, #2563eb);
        color: #fff;
    }

    .dasbor-perhatian {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(16rem, 1fr));
        gap: .75rem;
    }

    .dasbor-perhatian__item {
        display: flex;
        align-items: flex-start;
        gap: .75rem;
        padding: .875rem;
        border: 1px solid var(--gray-200, #e5e7eb);
        border-radius: .75rem;
        background: #fff;
        color: inherit;
        text-decoration: none;
    }

    .dasbor-perhatian__item:hover {
        border-color: var(--primary-400, #60a5fa);
        box-shadow: 0 1px 3px rgb(0 0 0 / .08);
    }

    .dasbor-perhatian__ikon {
        display: flex;
        flex: 0 0 2.25rem;
        align-items: center;
        justify-content: center;
        width: 2.25rem;
        height: 2.25rem;
        border-radius: .5rem;
        background: var(--warning-50, #fffbeb);
        color: var(--warning-600, #d97706);
    }

    .dasbor-perhatian__isi {
        min-width: 0;
        flex: 1;
    }

    .dasbor-perhatian__kepala {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: .5rem;
    }

    .dasbor-perhatian__judul {
        font-size: .875rem;
        font-weight: 600;
    }

    .dasbor-perhatian__jumlah {
        flex: 0 0 auto;
        padding: .1rem .5rem;
        border-radius: 999px;
        background: var(--warning-50, #fffbeb);
        color: var(--warning-700, #b45309);
        font-size: .75rem;
        font-weight: 700;
    }

    .dasbor-perhatian__pesan {
        display: block;
        margin-top: .25rem;
        color: var(--gray-500, #6b7280);
        font-size: .75rem;
        line-height: 1.5;
    }

    .dasbor-perhatian__kosong {
        display: flex;
        align-items: center;
        gap: .75rem;
        padding: 1rem;
        border-radius: .75rem;
        background: var(--success-50, #ecfdf5);
        color: var(--success-700, #047857);
        font-size: .875rem;
    }

    .dasbor-domain__baris {
        margin-bottom: .75rem;
    }

    .dasbor-domain__baris:last-child {
        margin-bottom: 0;
    }

    .dasbor-domain__kepala {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: .75rem;
        margin-bottom: .25rem;
        font-size: .875rem;
    }

    .dasbor-domain__nama {
        overflow: hidden;
        font-weight: 600;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .dasbor-domain__angka {
        flex: 0 0 auto;
        color: var(--gray-500, #6b7280);
        font-size: .75rem;
    }

    .dasbor-domain__track {
        height: .5rem;
        overflow: hidden;
        border-radius: 999px;
        background: var(--gray-100, #f3f4f6);
    }

    .dasbor-domain__isi {
        height: 100%;
        border-radius: 999px;
        background: var(--primary-500, #3b82f6);
    }

    .dasbor-heatmap__scroll {
        overflow-x: auto;
    }

    .dasbor-heatmap__inner {
        min-width: 34rem;
    }

    .dasbor-heatmap__baris {
        display: grid;
        grid-template-columns: 2.2rem repeat(24, minmax(0, 1fr));
        align-items: center;
        gap: .25rem;
        margin-bottom: .25rem;
    }

    .dasbor-heatmap__jam {
        color: var(--gray-400, #9ca3af);
        font-size: .6rem;
        text-align: center;
    }

    .dasbor-heatmap__label {
        color: var(--gray-500, #6b7280);
        font-size: .7rem;
        font-weight: 500;
    }

    .dasbor-heatmap__sel {
        height: 1rem;
        border-radius: .125rem;
        background: var(--primary-500, #3b82f6);
        box-shadow: inset 0 0 0 1px var(--gray-200, #e5e7eb);
    }

    .dasbor-kosong {
        margin: 0;
        color: var(--gray-500, #6b7280);
        font-size: .875rem;
    }

    .dark .dasbor-periode,
    .dark .dasbor-perhatian__item {
        border-color: var(--gray-700, #374151);
        background: var(--gray-900, #111827);
        color: var(--gray-100, #f3f4f6);
    }

    .dark .dasbor-periode__tautan:hover {
        background: var(--gray-800, #1f2937);
    }
</style>
