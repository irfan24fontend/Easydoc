<form method="POST" action="{{ route('locale.update') }}" class="locale-switcher" aria-label="{{ __('Language') }}">
    @csrf
    <button type="submit" name="locale" value="en" class="locale-switcher-option {{ app()->isLocale('en') ? 'is-active' : '' }}" aria-pressed="{{ app()->isLocale('en') ? 'true' : 'false' }}" title="English">EN</button>
    <button type="submit" name="locale" value="bn" class="locale-switcher-option {{ app()->isLocale('bn') ? 'is-active' : '' }}" aria-pressed="{{ app()->isLocale('bn') ? 'true' : 'false' }}" title="বাংলা">বাংলা</button>
</form>

<style>
    .locale-switcher {
        display: inline-flex; align-items: center; gap: 2px; margin: 0; padding: 3px;
        border: 1px solid #e2e8f0; border-radius: 10px; background: #f8fafc; flex: none;
    }
    .locale-switcher-option {
        min-height: 30px; padding: 0.25rem 0.55rem; border: 0; border-radius: 7px;
        background: transparent; color: #475569; font: inherit; font-size: 0.75rem;
        font-weight: 700; cursor: pointer; white-space: nowrap;
    }
    .locale-switcher-option:hover { background: #e2e8f0; }
    .locale-switcher-option.is-active { background: #0c8c84; color: #fff; }
    .locale-switcher-option:focus-visible { outline: 2px solid #0c8c84; outline-offset: 2px; }
</style>
