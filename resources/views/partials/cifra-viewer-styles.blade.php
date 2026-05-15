<style>
    .cifra-linha { display: flex; flex-wrap: wrap; align-items: flex-end; gap: 0.15rem; margin-bottom: 0.45rem; }
    .cifra-linha--refrao { border-left: 3px solid #fbbf24; border-radius: 1rem; background: rgba(251, 191, 36, 0.1); margin: 0.15rem 0 0.55rem; padding: 0.55rem 0.75rem; }
    .cifra-linha--refrao .cifra-letra { color: #fde68a; font-weight: 850; }
    .cifra-segmento { display: inline-flex; flex-direction: column; align-items: flex-start; justify-content: flex-end; min-height: 2.65rem; }
    .cifra-acordes { min-height: 1.1rem; margin-bottom: 0.02rem; color: #f97316; font-weight: 800; font-size: 0.95rem; line-height: 1rem; letter-spacing: 0.01em; white-space: pre; }
    .cifra-acorde { display: inline-block; padding: 0 0.05rem; border-radius: 0.35rem; }
    .cifra-letra { color: #d1fae5; font-size: 1.08rem; line-height: 1.75rem; white-space: pre-wrap; }
    .cifra-marcacao { display: inline-flex; align-items: center; border-radius: 9999px; background: rgba(255, 255, 255, 0.12); color: #fef3c7; font-size: 0.78rem; font-weight: 800; letter-spacing: 0.08em; text-transform: uppercase; padding: 0.45rem 0.85rem; margin: 1rem 0 0.75rem; }
    .cifra-marcacao--refrao { background: rgba(16, 185, 129, 0.18); color: #a7f3d0; font-weight: 950; }
    @media (max-width: 640px) {
        .cifra-linha { gap: 0.1rem; margin-bottom: 0.58rem; }
        .cifra-segmento { min-height: 2.45rem; max-width: 100%; }
        .cifra-acordes { font-size: 0.86rem; line-height: 0.95rem; white-space: normal; }
        .cifra-letra { font-size: 1rem; line-height: 1.65rem; overflow-wrap: anywhere; }
    }
</style>
