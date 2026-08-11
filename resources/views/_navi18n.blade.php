{{-- Language Switcher Dropdown --}}
<li id="navLang" class="nav-item dropdown">
    <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
        {{ $availableLanguages[$appLocale] }}
    </a>
    <ul class="dropdown-menu">
    @foreach ($availableLanguages as $locale => $language)
        @if ($locale != $appLocale)
        <li>
            <a class="dropdown-item" href="{{ route('lang.switch', $locale) }}">{{ $language }}</a>
        </li>
        @endif
    @endforeach
    </ul>
</li>
