@props([
    'title' => null,
    'description' => null,
    'image' => null,
    'type' => 'website',
    'robots' => null,
])

@php
    use Illuminate\Support\Str;

    $siteName = config('app.name');
    $organization = config('seo.organization_name');

    $sectionTitle = trim($__env->yieldContent('title', false));
    $resolvedTitle = $title ?? ($sectionTitle !== '' ? $sectionTitle : null);

    if ($resolvedTitle) {
        $documentTitle = Str::contains($resolvedTitle, $siteName)
            ? $resolvedTitle
            : "{$resolvedTitle} | {$siteName}";
    } else {
        $documentTitle = "{$siteName} — {$organization}";
    }

    $metaDescription = $description
        ?? trim($__env->yieldContent('meta_description', false))
        ?: config('seo.description');

    $canonical = url()->current();
    $faviconDir = trim(config('seo.favicon_dir', 'images/brand'), '/');

    $absoluteAsset = static fn (string $path): string => url(asset(ltrim($path, '/')));

    $ogImagePath = $image ?? config('seo.og_image');
    $ogImageUrl = str_starts_with($ogImagePath, 'http')
        ? $ogImagePath
        : $absoluteAsset($ogImagePath);

    $logoUrl = $absoluteAsset(config('seo.logo'));
    $robotsContent = $robots ?? trim($__env->yieldContent('meta_robots', false)) ?: null;
@endphp

<meta name="description" content="{{ $metaDescription }}">
<link rel="canonical" href="{{ $canonical }}">
@if ($robotsContent)
    <meta name="robots" content="{{ $robotsContent }}">
@endif

<link rel="icon" href="{{ $absoluteAsset("{$faviconDir}/favicon.ico") }}" sizes="any">
<link rel="icon" type="image/png" sizes="32x32" href="{{ $absoluteAsset("{$faviconDir}/favicon-32x32.png") }}">
<link rel="icon" type="image/png" sizes="16x16" href="{{ $absoluteAsset("{$faviconDir}/favicon-16x16.png") }}">
<link rel="apple-touch-icon" sizes="180x180" href="{{ $absoluteAsset("{$faviconDir}/apple-touch-icon.png") }}">

<meta property="og:locale" content="{{ str_replace('_', '-', app()->getLocale()) }}">
<meta property="og:type" content="{{ $type }}">
<meta property="og:site_name" content="{{ $organization }}">
<meta property="og:title" content="{{ $documentTitle }}">
<meta property="og:description" content="{{ $metaDescription }}">
<meta property="og:url" content="{{ $canonical }}">
<meta property="og:image" content="{{ $ogImageUrl }}">
<meta property="og:image:alt" content="{{ $organization }}">

<meta name="twitter:card" content="summary">
<meta name="twitter:title" content="{{ $documentTitle }}">
<meta name="twitter:description" content="{{ $metaDescription }}">
<meta name="twitter:image" content="{{ $ogImageUrl }}">
@if (config('seo.twitter_site'))
    <meta name="twitter:site" content="{{ config('seo.twitter_site') }}">
@endif

<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'GovernmentOrganization',
    'name' => $organization,
    'url' => url('/'),
    'logo' => $logoUrl,
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
</script>
