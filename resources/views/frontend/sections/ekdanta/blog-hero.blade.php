{{--
    Ekdanta · Blog Hero (Notebook). Load tracks bring the masthead and
    headline in; loops shuffle the stack of the latest posts and run the
    topic ticker; scrolling lifts the stack away. The search form submits to
    this page (the Article Grid filters by ?search=); with JS it filters the
    grid as you type (blog-grid.js). Phones (no design): text, search, a
    smaller stack, the ticker.
--}}
@php
    $d = $section->dataFor('desktop');
    $m = $section->dataFor('mobile');
    $u = fn ($n) => "calc({$n} * var(--u))";
    $stack = \App\Models\Post::query()->live()->with(['categories', 'featuredMedia', 'author'])->whereNotNull('featured_media_id')->latest('published_at')->limit(3)->get();
    $topics = array_values(array_filter(array_map('trim', preg_split('/\R/', (string) $d['topics']))));
    $glass = '<circle cx="11" cy="11" r="7"></circle><path d="M20 20l-4-4"></path>';
@endphp
<div class="ek ek-blog-hero" id="{{ $d['anchor'] ?: 'b-top' }}">
    @foreach (['desktop' => $d, 'mobile' => $m] as $comp => $t)
        @php
            $mob = $comp === 'mobile';
            $v = fn (array $pair) => $mob ? $pair[1] : $pair[0];
        @endphp
        <div class="ek-comp ek-comp--{{ $comp[0] }}" data-ek-comp="{{ $comp }}">
            <div class="ek-stage" data-ek-stage style="background: #FFFFFF">
                <div class="ek-art">
                    <div aria-hidden="true" style="position: absolute; inset: 0; background-image: linear-gradient(rgba(13,20,16,0.045) {{ $u(1) }}, transparent {{ $u(1) }}); background-size: 100% {{ $u(38) }}; background-position: 0 {{ $u(14) }}"></div>
                    <div data-m="bh-text" style="position: absolute; left: {{ $u($v([96, 24])) }}; top: {{ $u($v([130, 80])) }}; width: {{ $u($v([700, 342])) }}">
                        <div data-m="bh-masthead" style="display: flex; align-items: center; gap: {{ $u(12) }}; padding-bottom: {{ $u($v([14, 10])) }}; border-bottom: {{ $u(3) }} solid var(--tk-ink); width: {{ $u($v([600, 342])) }}"><span style="font-family: 'Poppins', sans-serif; font-weight: 800; font-size: max({{ $u($v([15, 12])) }}, 0.75rem); letter-spacing: 0.14em">{{ $t['masthead'] }}</span><span style="margin-left: auto; font-family: 'Poppins', sans-serif; font-weight: 600; font-size: max({{ $u($v([13, 12])) }}, 0.75rem); color: var(--tk-muted)">{{ $t['masthead_right'] }}</span></div>
                        <h1 style="margin: {{ $u($v([18, 12])) }} 0 0; font-family: 'Poppins', sans-serif; font-weight: 800; font-size: max({{ $u($v([122, 62])) }}, 0.8125rem); line-height: 0.98; letter-spacing: -0.05em">
                            <span style="display: block; overflow: hidden; padding-bottom: {{ $u($v([8, 4])) }}"><span data-m="bh-line" style="display: block">{{ $t['heading'] }}</span></span>
                            <span style="display: block; overflow: hidden; padding-bottom: {{ $u($v([8, 4])) }}"><span data-m="bh-line" style="display: block; color: var(--tk-green-text)">{{ $t['heading_2'] }}</span></span>
                        </h1>
                        <p data-m="bh-lede" style="margin: {{ $u($v([16, 10])) }} 0 0; {{ $mob ? '' : 'width: '.$u(560).';' }} font-size: max({{ $u($v([21, 16])) }}, 0.8125rem); line-height: 1.5; color: #3B4640">{{ $t['text'] }}</p>
                        <form data-m="bh-search" class="ek-blog-search" role="search" action="{{ url()->current() }}#b-posts" method="get" data-blog-search style="margin-top: {{ $u($v([28, 16])) }}; width: {{ $u($v([560, 342])) }}; height: {{ $u($v([60, 52])) }}; box-sizing: border-box; padding: 0 {{ $u(8) }} 0 {{ $u($v([22, 18])) }}; border-radius: 999px; background: var(--tk-paper); display: flex; align-items: center; gap: {{ $u($v([12, 8])) }}">
                            <svg viewBox="0 0 24 24" fill="none" stroke="#56605A" stroke-width="2.4" stroke-linecap="round" aria-hidden="true" style="width: {{ $u($v([20, 18])) }}; height: {{ $u($v([20, 18])) }}; flex: none">{!! $glass !!}</svg>
                            <input type="search" name="search" value="{{ request('search') }}" aria-label="{{ $d['search_label'] }}" placeholder="{{ $t['placeholder'] }}" style="flex: 1; min-width: 0; height: {{ $u($v([44, 40])) }}; border: 0; background: none; font-size: max({{ $u($v([17, 15])) }}, 0.8125rem); color: var(--tk-ink)">
                            <button type="submit" class="ek-press" style="height: {{ $u($v([44, 38])) }}; padding: 0 {{ $u($v([20, 14])) }}; border: 0; border-radius: 999px; background: var(--tk-ink); color: #FFFFFF; font-family: 'Poppins', sans-serif; font-weight: 600; font-size: max({{ $u($v([15, 14])) }}, 0.8125rem); cursor: pointer">{{ $t['search_button'] }}</button>
                        </form>
                    </div>

                    @if ($stack->isNotEmpty())
                        <div aria-hidden="true" data-m="bh-stack" style="position: absolute; left: {{ $u($v([900, 110])) }}; top: {{ $u($v([200, 430])) }}; width: {{ $u($v([380, 171])) }}; height: {{ $u($v([470, 212])) }}">
                            <div style="position: absolute; left: 0; top: 0; width: {{ $u(380) }}; height: {{ $u(470) }}; {{ $mob ? 'transform: scale(0.45); transform-origin: 0 0' : '' }}">
                                @foreach ($stack as $post)
                                    <div data-m="bh-card" style="position: absolute; inset: 0; border-radius: {{ $u(26) }}; overflow: hidden; background: #FFFFFF; box-shadow: 0 {{ $u(40) }} {{ $u(80) }} {{ $u(-36) }} rgba(13,20,16,0.55), inset 0 0 0 {{ $u(1) }} rgba(13,20,16,0.06)">
                                        <div style="height: {{ $u(260) }}; background: var(--tk-paper) url({{ $post->featuredMedia?->url }}) center top / cover no-repeat"></div>
                                        <div style="padding: {{ $u(22) }} {{ $u(24) }}; display: flex; flex-direction: column; gap: {{ $u(10) }}"><span style="align-self: flex-start; padding: {{ $u(5) }} {{ $u(10) }}; border-radius: 999px; background: #D9F2E1; font-family: 'Poppins', sans-serif; font-weight: 700; font-size: {{ $u(11) }}; letter-spacing: 0.06em; color: var(--tk-green-deep)">{{ mb_strtoupper((string) $post->categories->first()?->name) }}</span><span style="font-family: 'Poppins', sans-serif; font-weight: 800; font-size: {{ $u(23) }}; line-height: 1.2; letter-spacing: -0.03em">{{ $post->title }}</span><span style="font-size: {{ $u(14) }}; color: var(--tk-muted)">{{ str_replace('{author}', $post->displayAuthor(), (string) $d['byline']) }}</span></div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if ($topics)
                        <div aria-hidden="true" style="position: absolute; left: 0; right: 0; top: {{ $u($v([790, 770])) }}; height: {{ $u($v([62, 52])) }}; background: var(--tk-ink); color: #FFFFFF; overflow: hidden; display: flex; align-items: center">
                            <div data-m="bh-ticker" style="display: flex; white-space: nowrap; font-family: 'Poppins', sans-serif; font-weight: 700; font-size: {{ $u($v([20, 16])) }}; letter-spacing: -0.01em; width: max-content; padding-left: {{ $u(22) }}">
                                @foreach ([...$topics, ...$topics] as $topic)<span style="display: inline-flex; align-items: center; gap: {{ $u(22) }}; padding-right: {{ $u(22) }}">{{ $topic }}<span style="width: {{ $u(8) }}; height: {{ $u(8) }}; border-radius: 999px; background: var(--tk-green)"></span></span>@endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endforeach
</div>
