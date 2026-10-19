{{--
    Phones and tablets: a slim bar under the header showing the part being
    read and the minutes left (article.js fills it). It sits at the top of
    the text column and sticks there (sticky, not fixed: .ek is a size
    container) until the text ends. Decoration: the contents list says the
    same to screen readers (aria-current). $first: [num?, label], $left.
--}}
<div class="ek-ab__now-wrap" aria-hidden="true">
    <div class="ek-ab__now" data-now>
        <span class="ek-ab__now-num" data-now-num>{{ $first['num'] ?? '01' }}</span>
        <span class="ek-ab__now-title" data-now-title>{{ $first['label'] }}</span>
        <span class="ek-ab__now-left" data-now-left>{{ $left }}</span>
        <span class="ek-ab__now-bar" data-now-bar></span>
    </div>
</div>
