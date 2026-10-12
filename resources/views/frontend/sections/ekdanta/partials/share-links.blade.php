{{--
    Share an article: WhatsApp, LinkedIn and copy link (article.js copies
    the address and says so: the button's name changes, and the share
    row's [data-copy-status] line, inside [data-share], gets the note).
    $t: the section's labels; $btn: button style; $icon: [whatsapp,
    linkedin, copy] sizes in design px.
--}}
<a class="ek-press" href="https://wa.me/?text={{ rawurlencode($title.' '.$url) }}" target="_blank" rel="noopener" aria-label="{{ $t['share_whatsapp'] }}" style="{{ $btn }}"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" style="width: {{ ek_u($icon[0]) }}; height: {{ ek_u($icon[0]) }}"><path d="M21 12a8 8 0 0 1-11.6 7.1L4 20.5l1.4-5A8 8 0 1 1 21 12z"></path></svg></a>
<a class="ek-press" href="https://www.linkedin.com/sharing/share-offsite/?url={{ rawurlencode($url) }}" target="_blank" rel="noopener" aria-label="{{ $t['share_linkedin'] }}" style="{{ $btn }}"><svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" style="width: {{ ek_u($icon[1]) }}; height: {{ ek_u($icon[1]) }}"><path d="M4 9h3.6v11H4zM5.8 3.5a2.1 2.1 0 1 1 0 4.2 2.1 2.1 0 0 1 0-4.2zM9.8 9h3.4v1.6c.5-.9 1.7-1.9 3.5-1.9 3.7 0 4.3 2.4 4.3 5.5V20h-3.6v-5.1c0-1.3 0-2.9-1.8-2.9s-2.1 1.4-2.1 2.8V20H9.8z"></path></svg></a>
<button type="button" class="ek-press" data-copy-link="{{ $url }}" data-copied="{{ $t['copied_label'] }}" aria-label="{{ $t['copy_label'] }}" style="{{ $btn }}"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" style="width: {{ ek_u($icon[2]) }}; height: {{ ek_u($icon[2]) }}"><path d="M10 14a4 4 0 0 0 5.7 0l3-3a4 4 0 0 0-5.7-5.7l-1 1"></path><path d="M14 10a4 4 0 0 0-5.7 0l-3 3a4 4 0 0 0 5.7 5.7l1-1"></path></svg></button>
