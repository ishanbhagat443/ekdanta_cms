{{--
    Scroll animation controls for one section, with a tab per device.

    Dropdowns and sliders only — an admin picks an effect, never writes code.
    Tablet and mobile inherit desktop until they are changed, so the ordinary
    case (one effect everywhere) means filling in one tab.

    Vars: $section, $sid
--}}
@php
    use App\Support\SectionAnimation;

    $animDevices = [
        'desktop' => ['label' => 'Desktop', 'icon' => 'fa-desktop'],
        'tablet' => ['label' => 'Tablet', 'icon' => 'fa-tablet-screen-button'],
        'mobile' => ['label' => 'Mobile', 'icon' => 'fa-mobile-screen'],
    ];
@endphp

<hr>
<div class="d-flex align-items-center mb-2">
    <p class="text-muted small mb-0">
        <i class="fas fa-wand-magic-sparkles mr-1" aria-hidden="true"></i>Scroll animation
    </p>
    <small class="text-muted ml-auto">Plays when the section scrolls into view</small>
</div>

<ul class="nav nav-pills nav-sm mb-2" role="tablist">
    @foreach ($animDevices as $animDevice => $meta)
        <li class="nav-item">
            <a class="nav-link py-1 px-2 {{ $loop->first ? 'active' : '' }}" data-toggle="pill"
               href="#anim_{{ $sid }}_{{ $animDevice }}" role="tab">
                <i class="fas {{ $meta['icon'] }} mr-1" aria-hidden="true"></i><small>{{ $meta['label'] }}</small>
            </a>
        </li>
    @endforeach
</ul>

<div class="tab-content">
    @foreach ($animDevices as $animDevice => $meta)
        @php
            $anim = SectionAnimation::forDevice((array) $section->style, $animDevice);
            $inheritsDesktop = SectionAnimation::inherits((array) $section->style, $animDevice);
        @endphp

        <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}"
             id="anim_{{ $sid }}_{{ $animDevice }}" role="tabpanel">

            @if ($inheritsDesktop)
                <p class="text-muted small mb-2">
                    <i class="fas fa-link mr-1" aria-hidden="true"></i>Following the Desktop settings, so
                    these are locked. Pick an effect above to give
                    {{ strtolower($meta['label']) }} its own — including its own speed and timing.
                </p>
            @endif

            <div class="form-row">
                <div class="col-12 col-md-6">
                    <label class="mb-0" for="anim_preset_{{ $sid }}_{{ $animDevice }}"><small>Animation</small></label>
                    <select name="style[anim][{{ $animDevice }}][preset]" class="form-control form-control-sm"
                            id="anim_preset_{{ $sid }}_{{ $animDevice }}">
                        {{-- Tablet and mobile default to following desktop, so picking
                             one effect on the Desktop tab animates every screen. --}}
                        @if ($animDevice !== 'desktop')
                            <option value="{{ SectionAnimation::INHERIT }}" @selected($inheritsDesktop)>
                                Same as desktop
                            </option>
                        @endif
                        @foreach (SectionAnimation::PRESETS as $presetKey => $presetLabel)
                            <option value="{{ $presetKey }}"
                                    @selected(! $inheritsDesktop && $anim['preset'] === $presetKey)>{{ $presetLabel }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-6">
                    <label class="mb-0" for="anim_ease_{{ $sid }}_{{ $animDevice }}"><small>Movement style</small></label>
                    <select name="style[anim][{{ $animDevice }}][ease]" class="form-control form-control-sm"
                            id="anim_ease_{{ $sid }}_{{ $animDevice }}" @disabled($inheritsDesktop)>
                        @foreach (SectionAnimation::EASINGS as $easeKey => $easeLabel)
                            <option value="{{ $easeKey }}" @selected($anim['ease'] === $easeKey)>{{ $easeLabel }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="form-row mt-2">
                <div class="col-6 col-md-4">
                    <label class="mb-0" for="anim_duration_{{ $sid }}_{{ $animDevice }}"><small>Speed (ms)</small></label>
                    <input type="number" class="form-control form-control-sm"
                           id="anim_duration_{{ $sid }}_{{ $animDevice }}"
                           name="style[anim][{{ $animDevice }}][duration]" @disabled($inheritsDesktop)
                           min="{{ SectionAnimation::DURATION_RANGE[0] }}"
                           max="{{ SectionAnimation::DURATION_RANGE[1] }}" step="50"
                           value="{{ $anim['duration'] }}">
                </div>
                <div class="col-6 col-md-4">
                    <label class="mb-0" for="anim_delay_{{ $sid }}_{{ $animDevice }}"><small>Wait first (ms)</small></label>
                    <input type="number" class="form-control form-control-sm"
                           id="anim_delay_{{ $sid }}_{{ $animDevice }}"
                           name="style[anim][{{ $animDevice }}][delay]" @disabled($inheritsDesktop)
                           min="{{ SectionAnimation::DELAY_RANGE[0] }}"
                           max="{{ SectionAnimation::DELAY_RANGE[1] }}" step="50"
                           value="{{ $anim['delay'] }}">
                </div>
                <div class="col-12 col-md-4">
                    <label class="mb-0" for="anim_stagger_{{ $sid }}_{{ $animDevice }}">
                        <small>Stagger children (ms)</small>
                    </label>
                    <input type="number" class="form-control form-control-sm"
                           id="anim_stagger_{{ $sid }}_{{ $animDevice }}"
                           name="style[anim][{{ $animDevice }}][stagger]" @disabled($inheritsDesktop)
                           min="{{ SectionAnimation::STAGGER_RANGE[0] }}"
                           max="{{ SectionAnimation::STAGGER_RANGE[1] }}" step="10"
                           value="{{ $anim['stagger'] }}">
                    <small class="form-text text-muted mt-0">0 = the whole section moves together.</small>
                </div>
            </div>

            <div class="custom-control custom-switch mt-2">
                <input type="hidden" name="style[anim][{{ $animDevice }}][once]" value="0">
                <input type="checkbox" class="custom-control-input"
                       id="anim_once_{{ $sid }}_{{ $animDevice }}"
                       name="style[anim][{{ $animDevice }}][once]" value="1" @checked($anim['once']) @disabled($inheritsDesktop)>
                <label class="custom-control-label" for="anim_once_{{ $sid }}_{{ $animDevice }}">
                    <small>Play once (off = replay every time it scrolls into view)</small>
                </label>
            </div>
        </div>
    @endforeach
</div>

<p class="text-muted small mt-2 mb-0">
    <i class="fas fa-circle-info mr-1" aria-hidden="true"></i>Visitors who ask their device for
    reduced motion always see the content straight away, with no animation.
</p>
