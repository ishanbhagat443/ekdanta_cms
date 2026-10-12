{{--
    Ekdanta · Brief Builder. A Forms module form, one step at a time
    (brief-builder.js): options as chips, hidden fields that fill
    themselves, a summary, then "Send" saves the enquiry and opens
    WhatsApp with the answers. Without JS every step shows and the form
    posts normally (the server then opens WhatsApp). Scroll assembles the
    card (motion tracks); the road and walker follow the step (--step).
--}}
@php
    $d = $section->viewData('desktop');
    $m = $section->viewData('mobile');
    $form = \App\Models\Form::query()->where('slug', $d['form'] ?: 'ekdanta-brief')->where('is_active', true)->with('fields')->first();
    $visible = $form ? $form->fields->where('type', '!=', 'hidden') : collect();
    $steps = $visible->groupBy(fn ($f) => $f->step())->sortKeys()->values();
    $total = max(1, $steps->count());
    $words = (array) ($d['steps'] ?? []);
    $mark = media_ref($d['mark'] ?? null);
    $check = '<path d="M5 12.5l4.5 4.5L19 7.5"></path>';
    $chat = '<path d="M21 12a8 8 0 0 1-11.6 7.1L4 20.5l1.4-5A8 8 0 1 1 21 12z"></path>';
    $arrow = '<path d="M5 12h14M13 6l6 6-6 6"></path>';
    $counter = fn (string $text) => str_replace(['{n}', '{total}'], ['<span data-brief-num>1</span>', e((string) $total)], e($text));
    // Confetti for the "sent" moment: the design's seeded positions (Park–Miller, seed 5).
    $seed = 5;
    $rand = function () use (&$seed) { $seed = ($seed * 16807) % 2147483647; return ($seed - 1) / 2147483646; };
    $burst = [];
    for ($i = 0; $i < 18; $i++) {
        $burst[] = ['x' => round($rand() * 100, 1), 'c' => ['#F7C548', '#F2A230', '#2EB45D'][$i % 3], 'dur' => round(2.2 + $rand() * 1.6, 2), 'delay' => round(-$rand() * 2.5, 2)];
    }
@endphp
@if ($form)
<div class="ek ek-brief-builder" id="{{ $d['anchor'] ?: 'c-brief' }}">
    @foreach (['desktop' => $d, 'mobile' => $m] as $comp => $t)
        @php
            $mob = $comp === 'mobile';
            $v = fn (array $pair) => $mob ? $pair[1] : $pair[0];
            $k = $comp[0]; // ids are per composition (both are in the page)
            $spacing = $v([210.67, 96]);
            $pill = 'border-radius: 999px; font-family: \'Poppins\', sans-serif; font-weight: 600; cursor: pointer';
            // Text boxes: outline --tk-suffix (#85908A, 3:1), approved 10 Oct 2026 for WCAG 1.4.11 (drawn #CFD8D1).
            $input = 'height: '.ek_u($v([50, 46])).'; box-sizing: border-box; padding: 0 '.ek_u($v([18, 16])).'; border: '.ek_u(1.5).' solid var(--tk-suffix); border-radius: 999px; background: var(--tk-paper); font-family: \'Hind\', sans-serif; font-size: max('.ek_u($v([17, 16])).', 1rem); color: var(--tk-ink); width: 100%';
        @endphp
        <div class="ek-comp ek-comp--{{ $k }}" data-ek-comp="{{ $comp }}" style="--spacing: {{ $spacing }}; --walker: {{ $v([52, 44]) }}; --walk-from: {{ $v([-90, -70]) }}; --walk-shift: {{ $v([0, -2]) }}; --fall: {{ $v([940, 660]) }}">
            <div class="ek-stage" data-ek-stage style="background: var(--tk-paper)">
                <div class="ek-art">
                    <span aria-hidden="true" data-m="brief-word" style="position: absolute; left: 0; top: {{ ek_u($v([590, 640])) }}; white-space: nowrap; font-family: 'Poppins', sans-serif; font-weight: 800; font-size: {{ ek_u($v([300, 170])) }}; line-height: 1; letter-spacing: -0.06em; color: transparent; -webkit-text-stroke: {{ ek_u($v([2, 1.5])) }} #DCE3DA">{{ $d['bg_word'] }}</span>

                    <div style="position: absolute; left: {{ ek_u($v([96, 24])) }}; top: {{ ek_u($v([130, 82])) }}; width: {{ ek_u($v([420, 342])) }}; display: flex; flex-direction: column; gap: {{ ek_u($v([18, 6])) }}">
                        <span data-m="brief-eyebrow" style="font-family: 'Poppins', sans-serif; font-weight: 600; font-size: max({{ ek_u($v([14, 12])) }}, 0.75rem); letter-spacing: 0.12em; color: var(--tk-green-text-sm)">{{ $t['eyebrow'] }}</span>
                        <h2 data-m="brief-heading" style="margin: 0; font-family: 'Poppins', sans-serif; font-weight: 800; font-size: {{ ek_u($v([56, 31])) }}; line-height: {{ $v([1.02, 1.04]) }}; letter-spacing: -0.045em">{{ $t['heading'] }}</h2>
                        @php $call = e($t['call_before']).' <a href="'.e(link_href($d['call_link'] ?? '')).'" style="font-weight: 600; color: var(--tk-green-deep)">'.e($t['call_label']).'</a>'.e($t['call_after']); @endphp
                        <p data-m="brief-text" style="margin: 0; font-size: max({{ ek_u($v([19, 14])) }}, 0.8125rem); line-height: {{ $v([1.55, 1.45]) }}; color: #4A564F">{{ $t['text'] }}@if ($mob) {!! $call !!}@endif</p>
                        @unless ($mob)
                            <p data-m="brief-call" style="margin: {{ ek_u(12) }} 0 0; font-size: max({{ ek_u(17) }}, 0.8125rem); line-height: 1.5; color: #4A564F">{!! $call !!}</p>
                        @endunless
                    </div>

                    <div data-m="brief-card" class="ek-brief" data-ek-brief style="--step: 0; position: absolute; {{ $mob ? 'left: '.ek_u(12).'; right: '.ek_u(12).'; top: '.ek_u(214).'; height: '.ek_u(614).'; padding: '.ek_u(16).' '.ek_u(18) : 'left: '.ek_u(580).'; top: '.ek_u(110).'; width: '.ek_u(764).'; height: '.ek_u(690).'; padding: '.ek_u(36).' '.ek_u(40) }}; box-sizing: border-box; border-radius: {{ ek_u($v([28, 24])) }}; background: #FFFFFF; box-shadow: 0 {{ ek_u($v([30, 24])) }} {{ ek_u($v([60, 50])) }} {{ ek_u($v([-36, -30])) }} rgba(13,20,16,0.35); overflow: clip">
                        {{-- The road: one milestone per step, the walker stands on the current one. --}}
                        <div aria-hidden="true" style="position: relative; height: {{ ek_u($v([96, 74])) }}">
                            <div data-m="brief-track" style="position: absolute; left: {{ ek_u($v([26, 20])) }}; top: {{ ek_u($v([32, 26])) }}; width: {{ ek_u($spacing * ($total - 1)) }}; border-top: {{ ek_u(2) }} dashed #C9D3C7; transform-origin: 0 50%"></div>
                            <div class="ek-brief-fill" data-m="brief-fill" style="position: absolute; left: {{ ek_u($v([26, 20])) }}; top: {{ ek_u($v([31, 25])) }}; width: {{ ek_u($spacing) }}; height: {{ ek_u(4) }}; border-radius: {{ ek_u(2) }}; background: var(--tk-green); transform-origin: 0 50%"></div>
                            @foreach ($steps as $i => $fields)
                                <div data-m="brief-mile" class="ek-brief-mile {{ $i === 0 ? 'is-done' : '' }}" style="position: absolute; left: {{ ek_u($v([26, 20]) + $i * $spacing) }}; top: {{ ek_u($v([25, 20])) }}; width: {{ ek_u($v([120, 80])) }}; margin-left: {{ ek_u($v([-60, -40])) }}; display: flex; flex-direction: column; align-items: center; gap: {{ ek_u($v([30, 22])) }}">
                                    <span class="ek-brief-dot" style="width: {{ ek_u($v([16, 14])) }}; height: {{ ek_u($v([16, 14])) }}; border-radius: 50%; box-shadow: 0 0 0 {{ ek_u(4) }} #FFFFFF"></span>
                                    <span class="ek-brief-mile-label" style="font-family: 'Poppins', sans-serif; font-weight: 600; font-size: {{ ek_u($v([13, 11.5])) }}">{{ $words[$i]['short'] ?? '' }}</span>
                                </div>
                            @endforeach
                            <div class="ek-brief-walker" data-m="brief-walker" style="position: absolute; left: 0; top: 0; width: {{ ek_u($v([52, 44])) }}; height: {{ ek_u($v([52, 44])) }}">
                                <div data-m="brief-bob" style="width: 100%; height: 100%; border-radius: 50%; background: #FFFFFF; box-shadow: 0 {{ ek_u(8) }} {{ ek_u(18) }} {{ ek_u(-8) }} rgba(13,20,16,0.45); display: flex; align-items: center; justify-content: center">@if ($mark)<img src="{{ $mark->url }}" alt="" style="width: {{ ek_u($v([32, 27])) }}; height: auto">@endif</div>
                            </div>
                        </div>

                        <form class="ek-brief-form" method="POST" action="{{ route('front.form.submit', $form->slug) }}" novalidate data-brief-form data-wa-number="{{ business_whatsapp()['intl'] ?? '' }}" data-wa-intro="{{ $form->setting('whatsapp_intro', '') }}">
                            @csrf
                            <input type="hidden" name="_gsm_form" value="{{ $form->slug }}">
                            <input type="text" name="website" tabindex="-1" autocomplete="off" aria-hidden="true" style="position: absolute; left: -9999px">
                            @foreach ($form->fields->where('type', 'hidden') as $field)
                                <input type="hidden" name="{{ $field->name }}" value="" @if ($field->setting('autofill')) data-autofill="{{ $field->setting('autofill') }}" @endif>
                            @endforeach

                            @foreach ($steps as $i => $fields)
                                @php
                                    $title = ($words[$i]['title'] ?? '') ?: $fields->first()->label;
                                    $hint = $words[$i]['hint'] ?? '';
                                    $textual = $fields->every(fn ($f) => ! $f->hasOptions());
                                    $last = $i === $total - 1;
                                @endphp
                                <fieldset class="ek-brief-step {{ $i === 0 ? 'is-current' : '' }}" data-brief-step="{{ $i }}" style="margin: {{ ek_u($last ? $v([22, 10]) : $v([26, 14])) }} 0 0; padding: 0; border: 0; min-width: 0; display: flex; flex-direction: column; gap: {{ ek_u($last ? $v([14, 10]) : $v([10, 6])) }}">
                                    <legend style="float: left; width: 100%; margin: 0; padding: 0; font-family: 'Poppins', sans-serif; font-weight: 700; font-size: max({{ ek_u($v([30, 21])) }}, 0.8125rem); line-height: {{ $v([1.15, 1.2]) }}; letter-spacing: -0.03em">{{ $title }}</legend>
                                    @if ($hint !== '')
                                        <p style="margin: 0; font-size: max({{ ek_u($v([17, 14.5])) }}, 0.8125rem); color: var(--tk-muted)">{{ $hint }}</p>
                                    @endif

                                    @if ($textual)
                                        <div style="display: grid; grid-template-columns: {{ $mob ? '1fr' : 'repeat(2, minmax(0, 1fr))' }}; gap: {{ $mob ? ek_u(10) : ek_u(12).' '.ek_u(16) }}">
                                            @foreach ($fields->values() as $j => $field)
                                                @php $type = match ($field->type) { 'email' => 'email', 'phone' => 'tel', 'date' => 'date', default => 'text' }; @endphp
                                                <div data-brief-field="{{ $field->name }}" data-wa="{{ $field->setting('whatsapp', '') }}" style="display: flex; flex-direction: column; gap: {{ ek_u($v([6, 4])) }}; {{ ! $mob && $j === $fields->count() - 1 && $fields->count() % 2 === 1 ? 'grid-column: 1 / -1;' : '' }}">
                                                    <label for="b-{{ $field->name }}-{{ $k }}" style="font-family: 'Poppins', sans-serif; font-weight: 600; font-size: max({{ ek_u($v([14, 12.5])) }}, 0.8125rem)">{{ $field->label }}</label>
                                                    @if ($field->type === 'textarea')
                                                        <textarea name="{{ $field->name }}" id="b-{{ $field->name }}-{{ $k }}" rows="2" @if ($field->is_required) required @endif placeholder="{{ $field->placeholder }}" style="{{ $input }}; height: auto; padding-block: {{ ek_u(10) }}; border-radius: {{ ek_u(20) }}"></textarea>
                                                    @else
                                                        <input type="{{ $type }}" name="{{ $field->name }}" id="b-{{ $field->name }}-{{ $k }}" placeholder="{{ $field->placeholder }}" @if ($field->is_required) required @endif
                                                               @if ($type === 'tel') autocomplete="tel" pattern="[0-9+()\s.\-]{6,30}" @elseif ($type === 'email') autocomplete="email" @elseif (str_contains($field->name, 'name')) autocomplete="name" @elseif (str_contains($field->name, 'company') || str_contains($field->name, 'brand')) autocomplete="organization" @endif
                                                               style="{{ $input }}">
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        @foreach ($fields as $field)
                                            <div data-brief-field="{{ $field->name }}" data-wa="{{ $field->setting('whatsapp', '') }}" @if ($field->is_required) data-required @endif role="group" aria-label="{{ $title }}" style="margin-top: {{ ek_u($v([10, 8])) }}; display: flex; flex-wrap: wrap; gap: {{ ek_u($v([10, 8])) }}">
                                                @php $multi = $field->type === 'checkbox'; @endphp
                                                @foreach ((array) $field->options as $o => $option)
                                                    <span @if ($i === 0) data-m="brief-chip" @endif style="display: inline-flex">
                                                        <input class="ek-brief-check" type="{{ $multi ? 'checkbox' : 'radio' }}" name="{{ $field->name }}{{ $multi ? '[]' : '' }}" value="{{ $option }}" id="b-{{ $field->name }}-{{ $o }}-{{ $k }}" @if ($field->is_required && ! $multi) required @endif>
                                                        <label class="ek-brief-chip" for="b-{{ $field->name }}-{{ $o }}-{{ $k }}" style="min-height: {{ ek_u($v([48, 42])) }}; box-sizing: border-box; padding: 0 {{ ek_u($v([18, 14])) }}; border: {{ ek_u(1.5) }} solid var(--tk-ring); display: inline-flex; align-items: center; gap: {{ ek_u($v([8, 6])) }}; font-size: max({{ ek_u($v([15, 13.5])) }}, 0.8125rem); {{ $pill }}"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" style="width: {{ ek_u($v([16, 14])) }}; height: {{ ek_u($v([16, 14])) }}">{!! $check !!}</svg>{{ $option }}</label>
                                                    </span>
                                                @endforeach
                                            </div>
                                        @endforeach
                                    @endif

                                    @if ($last)
                                        <div class="ek-brief-js" style="padding: {{ $mob ? ek_u(10).' '.ek_u(14) : ek_u(14).' '.ek_u(18) }}; border-radius: {{ ek_u($v([16, 14])) }}; background: #E6F4EA; display: flex; flex-direction: column; gap: {{ ek_u($v([4, 2])) }}">
                                            <span style="font-family: 'Poppins', sans-serif; font-weight: 600; font-size: max({{ ek_u($v([12, 11])) }}, 0.75rem); letter-spacing: 0.1em; color: var(--tk-green-text-sm)">{{ $t['summary_label'] }}</span>
                                            <span data-brief-summary style="font-size: max({{ ek_u($v([16, 13.5])) }}, 0.8125rem); line-height: {{ $v([1.45, 1.4]) }}; color: var(--tk-dark-4)"></span>
                                        </div>
                                        <a class="ek-press ek-brief-js" href="#" target="_blank" rel="noopener" data-brief-send style="{{ $mob ? '' : 'align-self: flex-start;' }} height: {{ ek_u($v([56, 50])) }}; box-sizing: border-box; padding: 0 {{ ek_u($v([26, 20])) }}; display: {{ $mob ? 'flex; justify-content: center' : 'inline-flex' }}; align-items: center; gap: {{ ek_u(10) }}; background: var(--tk-green-deep); color: #FFFFFF; text-decoration: none; font-size: max({{ ek_u($v([16, 15])) }}, 0.8125rem); {{ $pill }}"><svg viewBox="0 0 24 24" fill="none" stroke="#FFFFFF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" style="width: {{ ek_u(18) }}; height: {{ ek_u(18) }}">{!! $chat !!}</svg>{{ $t['send_label'] }}</a>
                                        <button type="submit" class="ek-brief-nojs" style="align-self: flex-start; height: {{ ek_u($v([56, 50])) }}; padding: 0 {{ ek_u($v([26, 20])) }}; border: 0; background: var(--tk-green-deep); color: #FFFFFF; font-size: max({{ ek_u($v([16, 15])) }}, 0.8125rem); {{ $pill }}">{{ $t['send_label'] }}</button>
                                    @endif
                                </fieldset>
                            @endforeach
                        </form>

                        <div class="ek-brief-sent" data-brief-sent tabindex="-1" style="margin-top: {{ ek_u(40) }}; flex-direction: column; align-items: center; gap: {{ ek_u($v([14, 12])) }}; text-align: center">
                            <p style="margin: 0; font-family: 'Poppins', sans-serif; font-weight: 800; font-size: {{ ek_u($v([96, 60])) }}; line-height: 1.2; color: var(--tk-green-text)">{{ $t['sent_title'] }}</p>
                            <p style="margin: 0; max-width: {{ ek_u(520) }}; font-size: max({{ ek_u($v([19, 16])) }}, 0.8125rem); line-height: {{ $v([1.55, 1.5]) }}; color: #3B4640">{{ $form->success_message }}</p>
                            <div style="margin-top: {{ ek_u($v([10, 8])) }}; display: flex; {{ $mob ? 'flex-direction: column; width: 100%;' : 'align-items: center;' }} gap: {{ ek_u($v([10, 12])) }}">
                                @if ($form->setting('then_page'))
                                    <a class="ek-press" href="{{ link_href($form->setting('then_page')) }}" style="height: {{ ek_u(50) }}; box-sizing: border-box; padding: 0 {{ ek_u(22) }}; display: {{ $mob ? 'flex; justify-content: center' : 'inline-flex' }}; align-items: center; gap: {{ ek_u(8) }}; background: var(--tk-green); color: var(--tk-ink); text-decoration: none; font-size: max({{ ek_u(15) }}, 0.8125rem); {{ $pill }}">{{ $form->setting('then_label', '') }}<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" style="width: {{ ek_u(16) }}; height: {{ ek_u(16) }}">{!! $arrow !!}</svg></a>
                                @endif
                                <button type="button" class="ek-press" data-brief-restart style="height: {{ ek_u($v([50, 48])) }}; padding: 0 {{ ek_u(22) }}; border: {{ ek_u(1.5) }} solid var(--tk-ink); background: transparent; color: var(--tk-ink); font-size: max({{ ek_u(15) }}, 0.8125rem); {{ $pill }}">{{ $t['restart_label'] }}</button>
                            </div>
                            <div aria-hidden="true" class="ek-brief-confetti">
                                @foreach (array_slice($burst, 0, $mob ? 14 : 18) as $p)
                                    <span style="left: {{ $p['x'] }}%; width: {{ ek_u($v([14, 12])) }}; height: {{ ek_u($v([9, 8])) }}; background: {{ $p['c'] }}; --dur: {{ $p['dur'] }}s; --delay: {{ $p['delay'] }}s"></span>
                                @endforeach
                            </div>
                        </div>

                        <div class="ek-brief-js ek-brief-nav" data-m="brief-nav" style="position: absolute; {{ $mob ? 'left: '.ek_u(18).'; right: '.ek_u(18).'; bottom: '.ek_u(16) : 'left: '.ek_u(40).'; right: '.ek_u(40).'; bottom: '.ek_u(32) }}; justify-content: space-between; align-items: center">
                            <button type="button" class="ek-press" data-brief-back aria-disabled="true" style="height: {{ ek_u($v([50, 44])) }}; padding: 0 {{ ek_u($v([20, 18])) }}; border: {{ ek_u(1.5) }} solid var(--tk-ink); background: transparent; color: var(--tk-ink); font-size: max({{ ek_u($v([15, 14])) }}, 0.8125rem); {{ $pill }}">{{ $t['back_label'] }}</button>
                            <span aria-live="polite" style="font-family: 'Poppins', sans-serif; font-weight: 600; font-size: max({{ ek_u($v([14, 13])) }}, 0.8125rem); color: var(--tk-muted)">{!! $counter($t['counter']) !!}</span>
                            <button type="button" class="ek-press" data-brief-next aria-disabled="true" style="height: {{ ek_u($v([50, 44])) }}; padding: 0 {{ ek_u($v([26, 22])) }}; border: 0; background: var(--tk-ink); color: #FFFFFF; font-size: max({{ ek_u($v([15, 14])) }}, 0.8125rem); {{ $pill }}">{{ $t['next_label'] }}</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>
@endif
