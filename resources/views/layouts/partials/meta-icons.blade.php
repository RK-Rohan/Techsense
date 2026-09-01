{{--
    Favicons and link-preview (Open Graph) tags.

    Shared by every layout so a link pasted into WhatsApp, Facebook or LinkedIn
    shows the TBL logo and company name. Without og:image the scrapers pick an
    arbitrary image off the page, which is how the old Sensor Shop logo kept
    appearing in previews.
--}}
@php
    $app_name = trim(config('app.name', 'TBL Engineering'));
    $page_title = trim($__env->yieldContent('title'));

    // Append the app name only when the page title does not already carry it,
    // otherwise a login page titled after the business repeats it in previews.
    if ($page_title === '' || strcasecmp($page_title, $app_name) === 0) {
        $og_title = $app_name;
    } elseif (stripos($page_title, $app_name) !== false) {
        $og_title = $page_title;
    } else {
        $og_title = $page_title . ' - ' . $app_name;
    }
    $og_description = $__env->yieldContent(
        'meta_description',
        'Business management and invoicing system for TBL Engineering.'
    );
@endphp

<link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
<link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
<link rel="icon" type="image/png" sizes="192x192" href="{{ asset('icon-192.png') }}">
<link rel="icon" type="image/png" sizes="512x512" href="{{ asset('icon-512.png') }}">

<meta property="og:type" content="website">
<meta property="og:site_name" content="{{ $app_name }}">
<meta property="og:title" content="{{ $og_title }}">
<meta property="og:description" content="{{ $og_description }}">
<meta property="og:url" content="{{ url()->current() }}">
<meta property="og:image" content="{{ asset('og-image.png') }}">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta property="og:image:type" content="image/png">

<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $og_title }}">
<meta name="twitter:description" content="{{ $og_description }}">
<meta name="twitter:image" content="{{ asset('og-image.png') }}">

<meta name="theme-color" content="#1b6ec2">
