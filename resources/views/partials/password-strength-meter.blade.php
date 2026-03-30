<div class="mt-3 rounded-2xl border border-gray-200 bg-gray-50 p-4" data-password-strength data-password-required="{{ !empty($required) ? 'true' : 'false' }}">
    <div class="flex items-center justify-between gap-3">
        <span class="text-xs font-bold uppercase tracking-wider text-gray-500">Forca da senha</span>
        <span class="text-sm font-semibold text-red-600" data-password-strength-label>Fraca</span>
    </div>

    <div class="mt-3 h-2 overflow-hidden rounded-full bg-gray-200">
        <div class="h-full w-0 rounded-full bg-red-500 transition-all duration-200" data-password-strength-bar></div>
    </div>

    <ul class="mt-4 space-y-2 text-xs text-gray-600">
        <li data-password-rule="length">Pelo menos 8 caracteres</li>
        <li data-password-rule="lower">Ao menos 1 letra minuscula</li>
        <li data-password-rule="upper">Ao menos 1 letra maiuscula</li>
        <li data-password-rule="number">Ao menos 1 numero</li>
        <li data-password-rule="symbol">Ao menos 1 caractere especial</li>
    </ul>
</div>
