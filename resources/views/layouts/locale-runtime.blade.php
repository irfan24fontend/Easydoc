@php
    $localeFile = lang_path(app()->getLocale().'.json');
    $localeMessages = is_file($localeFile) ? (json_decode(file_get_contents($localeFile), true) ?: []) : [];
    $localeExtraFile = lang_path('ui-'.app()->getLocale().'.json');
    $localeExtraMessages = is_file($localeExtraFile) ? (json_decode(file_get_contents($localeExtraFile), true) ?: []) : [];
    $localeMessages = array_merge($localeMessages, $localeExtraMessages);
@endphp
<script>
    window.easyDocLocale = @json(app()->getLocale());
    window.easyDocMessages = @json($localeMessages);
</script>
<script src="{{ asset('js/easydoc-locale.js') }}" defer></script>
