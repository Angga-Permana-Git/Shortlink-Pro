{!! '<' . '?xml version="1.0" encoding="UTF-8"?' . '>' !!}
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
@foreach ($daftar as $item)
    <url>
        <loc>{{ $item['url'] }}</loc>
        @if ($item['waktu'])
        <lastmod>{{ $item['waktu']->tz('UTC')->toAtomString() }}</lastmod>
        @endif
    </url>
@endforeach
</urlset>
