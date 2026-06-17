@php
    $title = 'DrivingFaith';
    $description = 'A calm back office for churches managing people, events, communication, governance, and outreach.';
    $image = asset('images/drivingfaith-icon-square.png');
    $keywords = 'church management, ministry software, church events, member directory, church communication';
    $canonical = request()->url();
@endphp

@if(isset($seo))
    @php
        $title = $seo['title'] ?? $title;
        $description = $seo['description'] ?? $description;
        $image = $seo['image'] ?? $image;
        $keywords = $seo['keywords'] ?? $keywords;
        $canonical = $seo['canonical'] ?? $canonical;
    @endphp
@endif

<title>{{ $title }}</title>
<meta name="description" content="{{ $description }}">
<meta name="keywords" content="{{ $keywords }}">
<link rel="canonical" href="{{ $canonical }}">
<!-- OG-->
<meta name="og:title" content="{{ $title }}">
<meta name="og:description" content="{{ $description }}">
<meta name="og:image" content="{{ $image }}">

<!-- Twitter Card Tags -->
<meta name="twitter:title" content="{{ $title }}">
<meta name="twitter:description" content="{{ $description }}">
<meta name="twitter:image" content="{{ $image }}">
{{--Default Meta Tags ( Non changable )--}}
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:site" content="@yourtwitterhandle">
