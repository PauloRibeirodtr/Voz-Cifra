<style>
    .cifra-linha { display: flex; flex-wrap: wrap; align-items: flex-end; gap: 0.14rem; margin-bottom: 0.34rem; }
    .cifra-linha--refrao { border-left: 4px solid #fbbf24; border-radius: 0; background: linear-gradient(90deg, rgba(251, 191, 36, 0.12), transparent); margin: 0.08rem 0 0.38rem; padding: 0.28rem 0 0.28rem 0.72rem; }
    .cifra-linha--refrao .cifra-letra { color: #fde68a; font-weight: 850; }
    .cifra-segmento { display: inline-flex; flex-direction: column; align-items: flex-start; justify-content: flex-end; min-height: 2.08rem; }
    .cifra-acordes { min-height: 0.74rem; margin-bottom: -0.04rem; color: #fca5a5; font-weight: 800; font-size: 0.9rem; line-height: 0.86rem; letter-spacing: 0.01em; white-space: pre; }
    .cifra-acorde { display: inline-block; padding: 0 0.05rem; border-radius: 0.35rem; transition: background-color 0.15s ease, color 0.15s ease; }
    .cifra-acorde:hover, .cifra-acorde.ativa { background: rgba(252, 165, 165, 0.16); color: #fee2e2; }
    .cifra-letra { color: #d1fae5; font-size: 1.08rem; line-height: 1.42rem; white-space: pre-wrap; }
    .cifra-marcacao { display: inline-flex; align-items: center; border-radius: 9999px; background: rgba(255, 255, 255, 0.12); color: #fef3c7; font-size: 0.78rem; font-weight: 800; letter-spacing: 0.08em; text-transform: uppercase; padding: 0.45rem 0.85rem; margin: 1rem 0 0.75rem; }
    .cifra-marcacao--refrao { background: rgba(16, 185, 129, 0.18); color: #a7f3d0; font-weight: 950; }
    @media (max-width: 640px) {
        .cifra-linha { gap: 0.1rem; margin-bottom: 0.42rem; }
        .cifra-segmento { min-height: 1.88rem; max-width: 100%; }
        .cifra-acordes { font-size: 0.86rem; line-height: 0.84rem; white-space: normal; }
        .cifra-letra { font-size: 1rem; line-height: 1.3rem; overflow-wrap: anywhere; }
    }
</style>
