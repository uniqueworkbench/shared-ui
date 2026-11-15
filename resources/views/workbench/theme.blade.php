{{--
    The colours of the page chrome, in <head>: the top bar and the left menu (.uw-chrome-top / -side / -side-lg, zinc-900
    unless set) and, in a portal, the owner's palette — its top bar, menu and the button colour that fills
    bt_primary (tailwind.config.js reads --bt-primary-*, "r g b"; apps rebuild their CSS with the shared config).
--}}
@php
    $brand = config('shared-ui.portal') && auth()->check() ? workbench()->portalBrand() : [];
    $hex = fn ($value, $fallback) => preg_match('/^#[0-9a-fA-F]{6}$/', (string) $value) ? strtolower($value) : $fallback;
    $top = $hex($brand['top_color'] ?? null, null);
    $side = $hex($brand['side_color'] ?? null, null);
    $button = $hex($brand['button_color'] ?? null, null);
    // A shade of the button colour: toward white (+) or black (-) by that share
    $rgb = fn (string $color) => [hexdec(substr($color, 1, 2)), hexdec(substr($color, 3, 2)), hexdec(substr($color, 5, 2))];
    $shade = function (string $color, float $by) use ($rgb) {
        return implode(' ', array_map(fn ($c) => (int) round($by >= 0 ? $c + (255 - $c) * $by : $c * (1 + $by)), $rgb($color)));
    };
    $steps = [50 => 0.94, 100 => 0.88, 200 => 0.76, 300 => 0.58, 400 => 0.36, 500 => 0.14, 600 => 0, 700 => -0.18, 800 => -0.34, 900 => -0.48];
@endphp
<style>
    .uw-chrome-top { background-color: var(--uw-chrome-top, #18181b); }
    .uw-chrome-side { background-color: var(--uw-chrome-side, #18181b); }
    @media (min-width: 1024px) { .uw-chrome-side-lg { background-color: var(--uw-chrome-side, #18181b); } }
    @if ($top || $side || $button)
    :root {
        @if ($top) --uw-chrome-top: {{ $top }}; @endif
        @if ($side) --uw-chrome-side: {{ $side }}; @endif
        @if ($button)
            @foreach ($steps as $step => $by) --bt-primary-{{ $step }}: {{ $shade($button, $by) }}; @endforeach
        @endif
    }
    @endif
</style>
