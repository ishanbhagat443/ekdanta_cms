<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateSettingsRequest;
use App\Services\SettingsService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class SettingController extends Controller
{
    public function __construct(protected SettingsService $settings)
    {
        $this->middleware('can:settings.manage');
    }

    public function edit(string $group): View
    {
        $schema = config("settings.{$group}");

        abort_if($schema === null, 404);

        return view('admin.settings.edit', [
            'group' => $group,
            'schema' => $schema,
            'groups' => config('settings'),
            'values' => $this->settings->group($group),
        ]);
    }

    public function update(UpdateSettingsRequest $request, string $group): RedirectResponse
    {
        abort_if(config("settings.{$group}") === null, 404);

        $this->settings->updateGroup(
            $group,
            $request->safe()->except('remove'),
            array_keys($request->input('remove', []))
        );

        return redirect()
            ->route('admin.settings.edit', $group)
            ->with('success', config("settings.{$group}.label").' saved.');
    }
}
