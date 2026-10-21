<style>
    :root { --wl-brand:#4f46e5; --wl-ink:#0f172a; --wl-muted:#64748b; --wl-line:#e5e9f0; --wl-bkash:#e2136e; }

    .wl-hero { position:relative; overflow:hidden; border-radius:22px; padding:1.5rem 1.8rem; color:#fff; margin-bottom:1.25rem;
        background:radial-gradient(1200px 300px at 90% -20%, rgba(255,255,255,.18), transparent 60%), linear-gradient(135deg,#4f46e5 0%,#6d28d9 55%,#7c3aed 100%);
        box-shadow:0 14px 34px rgba(79,70,229,.28); }
    .wl-hero::after { content:""; position:absolute; right:-60px; bottom:-80px; width:240px; height:240px; border-radius:50%; background:rgba(255,255,255,.07); }
    .wl-hero h1 { font-size:1.45rem; font-weight:800; letter-spacing:-.02em; margin:0; }
    .wl-hero p { margin:.3rem 0 0; opacity:.9; font-size:.86rem; max-width:620px; position:relative; z-index:1; }

    .wl-card { background:#fff; border:1px solid var(--wl-line); border-radius:18px; padding:1.25rem 1.35rem; box-shadow:0 1px 2px rgba(0,0,0,.03); height:100%; }
    .wl-card h2 { font-size:1rem; font-weight:800; color:var(--wl-ink); margin:0 0 .9rem; display:flex; align-items:center; gap:.55rem; }
    .wl-card h2 i { color:var(--wl-brand); }

    .wl-balance { font-size:2.2rem; font-weight:800; letter-spacing:-.03em; color:var(--wl-ink); line-height:1.1; }
    .wl-label { font-size:.72rem; text-transform:uppercase; letter-spacing:.07em; font-weight:700; color:var(--wl-muted); }

    .wl-stat { background:#fff; border:1px solid var(--wl-line); border-radius:16px; padding:1rem 1.15rem; height:100%; }
    .wl-stat .n { font-size:1.35rem; font-weight:800; color:var(--wl-ink); letter-spacing:-.02em; }

    .wl-badge { display:inline-flex; align-items:center; gap:.3rem; font-size:.72rem; font-weight:700; padding:.25rem .65rem; border-radius:999px; white-space:nowrap; }
    .wl-badge.pending { background:#fef3c7; color:#92400e; }
    .wl-badge.approved, .wl-badge.credit { background:#d1fae5; color:#065f46; }
    .wl-badge.rejected, .wl-badge.debit { background:#fee2e2; color:#991b1b; }

    .wl-table th { font-size:.7rem; text-transform:uppercase; letter-spacing:.06em; color:var(--wl-muted); font-weight:700; border-bottom:1px solid var(--wl-line); white-space:nowrap; }
    .wl-table td { vertical-align:middle; border-color:#f1f5f9; font-size:.9rem; }
    .wl-empty { text-align:center; color:var(--wl-muted); padding:2rem 1rem; font-size:.9rem; }
    .wl-empty i { font-size:1.8rem; color:#cbd5e1; display:block; margin-bottom:.5rem; }

    .wl-label-f { font-size:.78rem; font-weight:700; color:#334155; margin-bottom:.3rem; display:block; }
    .wl-err { color:#dc2626; font-size:.8rem; margin-top:.3rem; }
</style>
