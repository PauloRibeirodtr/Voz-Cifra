<div class="mt-3 rounded-2xl border border-gray-200 bg-gray-50 p-4" data-password-strength data-password-required="{{ !empty($required) ? 'true' : 'false' }}">
    <div class="flex items-center justify-between gap-3">
        <span class="text-xs font-bold uppercase tracking-wider text-gray-500">Forca da senha</span>
        <span class="text-sm font-semibold text-red-600" data-password-strength-label>Fraca</span>
    </div>

    <div class="mt-3 h-2 overflow-hidden rounded-full bg-gray-200">
        <div class="h-full w-0 rounded-full bg-red-500 transition-all duration-200" data-password-strength-bar></div>
    </div>

    <ul class="mt-4 space-y-2 text-xs text-gray-600">
        <li class="flex items-center gap-2" data-password-rule="length"><span class="inline-flex h-4 w-4 items-center justify-center rounded-full border border-gray-300 text-[10px]" data-password-rule-icon>•</span><span>Pelo menos 8 caracteres</span></li>
        <li class="flex items-center gap-2" data-password-rule="lower"><span class="inline-flex h-4 w-4 items-center justify-center rounded-full border border-gray-300 text-[10px]" data-password-rule-icon>•</span><span>Ao menos 1 letra minuscula</span></li>
        <li class="flex items-center gap-2" data-password-rule="upper"><span class="inline-flex h-4 w-4 items-center justify-center rounded-full border border-gray-300 text-[10px]" data-password-rule-icon>•</span><span>Ao menos 1 letra maiuscula</span></li>
        <li class="flex items-center gap-2" data-password-rule="number"><span class="inline-flex h-4 w-4 items-center justify-center rounded-full border border-gray-300 text-[10px]" data-password-rule-icon>•</span><span>Ao menos 1 numero</span></li>
        <li class="flex items-center gap-2" data-password-rule="symbol"><span class="inline-flex h-4 w-4 items-center justify-center rounded-full border border-gray-300 text-[10px]" data-password-rule-icon>•</span><span>Ao menos 1 caractere especial</span></li>
        <li class="flex items-center gap-2" data-password-match hidden><span class="inline-flex h-4 w-4 items-center justify-center rounded-full border border-gray-300 text-[10px]" data-password-match-icon>•</span><span data-password-match-text>As senhas precisam conferir</span></li>
    </ul>
</div>
