{{--
    The builder's one piece of persistent furniture: undo/redo, the save
    state, and the single Save button. Shared by the Page, Header and Footer
    builders so "am I saved?" is answered the same way everywhere.

    Vars:
      $formId      id of the settings form this Save submits
      $previewUrl  where "Open preview" goes
      $historyUrl  optional — omit on screens without version history
--}}
@php
    $historyUrl = $historyUrl ?? null;
@endphp

<div class="gsm-builder-bar" data-gsm-builder-bar>
    <div class="gsm-builder-bar__group" role="group" aria-label="Undo and redo">
        <button type="button" class="gsm-tool-btn" data-gsm-undo disabled
                title="Undo (Ctrl+Z)" aria-label="Undo last change" aria-keyshortcuts="Control+Z">
            <i class="fas fa-rotate-left" aria-hidden="true"></i>
        </button>
        <button type="button" class="gsm-tool-btn" data-gsm-redo disabled
                title="Redo (Ctrl+Shift+Z)" aria-label="Redo change" aria-keyshortcuts="Control+Shift+Z">
            <i class="fas fa-rotate-right" aria-hidden="true"></i>
        </button>
    </div>

    {{-- aria-live so a screen reader hears the save state change too; the
         icon carries the meaning alongside colour. --}}
    <p class="gsm-save-state" data-gsm-save-state data-state="idle" role="status" aria-live="polite">
        <i class="fas fa-circle-check" aria-hidden="true" data-gsm-save-icon></i>
        <span data-gsm-save-text>All changes saved</span>
    </p>

    <div class="gsm-builder-bar__actions">
        @if ($historyUrl)
            <button type="button" class="btn btn-outline-secondary btn-sm" data-toggle="modal"
                    data-target="#gsm-history-modal" data-gsm-history-open>
                <i class="fas fa-clock-rotate-left mr-1" aria-hidden="true"></i>Version history
            </button>
        @endif

        <a href="{{ $previewUrl }}" target="_blank" rel="noopener" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-up-right-from-square mr-1" aria-hidden="true"></i>Open preview
        </a>

        <button type="submit" form="{{ $formId }}" class="btn btn-primary btn-sm gsm-save-btn">
            <i class="fas fa-save mr-1" aria-hidden="true"></i>Save
        </button>
    </div>
</div>
