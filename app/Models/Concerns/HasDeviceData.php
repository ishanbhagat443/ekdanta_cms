<?php

declare(strict_types=1);

namespace App\Models\Concerns;

/**
 * Per-device content (data_desktop / data_tablet / data_mobile), shared by
 * Section and GlobalBlock: desktop holds every value, the other two only
 * what differs. An empty tablet value falls back to the phone's, then the
 * desktop's; an empty phone value to the desktop's.
 */
trait HasDeviceData
{
    public function dataFor(string $device): array
    {
        $desktop = (array) ($this->data_desktop ?? []);

        if ($device === 'desktop') {
            return $desktop;
        }

        $own = fn (string $d) => array_filter((array) ($this->{'data_'.$d} ?? []), fn ($v) => $v !== null && $v !== '');

        return array_replace($desktop, $device === 'tablet' ? $own('mobile') : [], $own($device));
    }

    /**
     * A device tab's own values (desktop: all of them) and the values its
     * empty fields use instead.
     *
     * @return array{0: array<string, mixed>, 1: array<string, mixed>}
     */
    public function ownAndInherited(string $device): array
    {
        return [(array) ($this->{'data_'.$device} ?? []), $device === 'desktop' ? [] : $this->dataFor($device === 'tablet' ? 'mobile' : 'desktop')];
    }
}
