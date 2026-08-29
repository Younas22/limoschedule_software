<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\PageSection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PageSectionController extends Controller
{
    public function create(Page $page): View
    {
        return view('admin.pages.sections.create', compact('page'));
    }

    public function store(Request $request, Page $page): RedirectResponse
    {
        $data = $this->validateSection($request);

        if ($request->hasFile('image')) {
            $data['image'] = $this->storeUpload($request->file('image'));
        }

        if ($request->hasFile('video')) {
            $data['video'] = $this->storeUpload($request->file('video'));
        }

        $data['sort_order'] = $page->sections()->max('sort_order') + 1;
        $data['is_active'] = true;

        $page->sections()->create($data);

        return redirect()
            ->route('admin.pages.edit', $page)
            ->with('status', 'Section added successfully.');
    }

    public function edit(Page $page, PageSection $section): View
    {
        return view('admin.pages.sections.edit', compact('page', 'section'));
    }

    public function update(Request $request, Page $page, PageSection $section): RedirectResponse
    {
        $data = $this->validateSection($request);

        if ($request->hasFile('image')) {
            $data['image'] = $this->storeUpload($request->file('image'), $section->image);
        } elseif ($request->boolean('remove_image')) {
            $this->deleteUpload($section->image);
            $data['image'] = null;
        }

        if ($request->hasFile('video')) {
            $data['video'] = $this->storeUpload($request->file('video'), $section->video);
        } elseif ($request->boolean('remove_video')) {
            $this->deleteUpload($section->video);
            $data['video'] = null;
        }

        $section->update($data);

        return redirect()
            ->route('admin.pages.edit', $page)
            ->with('status', 'Section updated successfully.');
    }

    public function destroy(Page $page, PageSection $section): RedirectResponse
    {
        $this->deleteUpload($section->image);
        $this->deleteUpload($section->video);

        foreach ($section->team_members as $member) {
            $this->deleteUpload($member['photo'] ?? null, 'team');
        }

        $section->delete();

        return back()->with('status', 'Section removed successfully.');
    }

    public function toggleStatus(Page $page, PageSection $section): RedirectResponse
    {
        $section->update(['is_active' => ! $section->is_active]);

        return back()->with('status', $section->is_active ? 'Section enabled.' : 'Section disabled.');
    }

    public function moveUp(Page $page, PageSection $section): RedirectResponse
    {
        $previous = $page->sections()->where('sort_order', '<', $section->sort_order)->orderByDesc('sort_order')->first();

        if ($previous) {
            DB::transaction(function () use ($section, $previous) {
                [$section->sort_order, $previous->sort_order] = [$previous->sort_order, $section->sort_order];
                $section->save();
                $previous->save();
            });
        }

        return back();
    }

    public function moveDown(Page $page, PageSection $section): RedirectResponse
    {
        $next = $page->sections()->where('sort_order', '>', $section->sort_order)->orderBy('sort_order')->first();

        if ($next) {
            DB::transaction(function () use ($section, $next) {
                [$section->sort_order, $next->sort_order] = [$next->sort_order, $section->sort_order];
                $section->save();
                $next->save();
            });
        }

        return back();
    }

    public function reorder(Request $request, Page $page): RedirectResponse
    {
        $data = $request->validate([
            'section_ids' => ['required', 'array'],
            'section_ids.*' => ['integer', Rule::exists('page_sections', 'id')->where('page_id', $page->id)],
        ]);

        DB::transaction(function () use ($data, $page) {
            foreach ($data['section_ids'] as $index => $sectionId) {
                $page->sections()->where('id', $sectionId)->update(['sort_order' => $index]);
            }
        });

        return back()->with('status', 'Section order updated.');
    }

    private function validateSection(Request $request): array
    {
        $type = $request->input('type');

        $data = $request->validate([
            'type' => ['required', Rule::in(array_keys(PageSection::TYPES))],
            'heading' => ['nullable', 'string', 'max:255'],
            'eyebrow' => ['nullable', 'string', 'max:60'],
            'subheading' => ['nullable', 'string', 'max:255'],
            'differentiator' => ['nullable', 'string', 'max:160'],
            'body' => [Rule::requiredIf($type === 'rich_text'), 'nullable', 'string'],
            'image' => ['nullable', 'image', 'max:4096'],
            'video' => ['nullable', 'file', 'mimetypes:video/mp4,video/webm,video/quicktime', 'max:25600'],
            'button_text' => ['nullable', 'string', 'max:100'],
            'button_url' => ['nullable', 'string', 'max:255'],
            'button_text_2' => ['nullable', 'string', 'max:100'],
            'button_url_2' => ['nullable', 'string', 'max:255'],
            'items' => [Rule::requiredIf(in_array($type, ['items', 'trust_badges', 'faq', 'stats', 'team', 'process'], true)), 'nullable', 'array'],
            'items.*.title' => ['required_if:type,items', 'required_if:type,trust_badges', 'required_if:type,process', 'nullable', 'string', 'max:255'],
            'items.*.description' => ['nullable', 'string', 'max:1000'],
            'items.*.icon' => ['nullable', 'string', 'max:50'],
            'items.*.link' => ['nullable', 'string', 'max:255'],
            'items.*.question' => ['required_if:type,faq', 'nullable', 'string', 'max:255'],
            'items.*.answer' => ['required_if:type,faq', 'nullable', 'string', 'max:1000'],
            'items.*.category' => ['nullable', 'string', 'max:100'],
            'items.*.label' => ['required_if:type,stats', 'nullable', 'string', 'max:100'],
            'items.*.value' => ['required_if:type,stats', 'nullable', 'integer', 'min:0', 'max:999999999'],
            'items.*.suffix' => ['nullable', 'string', 'max:10'],
            'items.*.name' => ['required_if:type,team', 'nullable', 'string', 'max:255'],
            'items.*.role' => ['nullable', 'string', 'max:100'],
            'items.*.bio' => ['nullable', 'string', 'max:500'],
            'items.*.photo' => ['nullable', 'image', 'max:2048'],
            'items.*.existing_photo' => ['nullable', 'string'],
            'testimonial_limit' => ['nullable', 'integer', 'min:1', 'max:24'],
            'testimonial_min_rating' => ['nullable', 'integer', 'min:1', 'max:5'],
            'fleet_limit' => ['nullable', 'integer', 'min:1', 'max:48'],
            'fleet_category_id' => ['nullable', 'integer', 'exists:vehicle_categories,id'],
            'route_limit' => ['nullable', 'integer', 'min:1', 'max:24'],
            'blog_limit' => ['nullable', 'integer', 'min:1', 'max:24'],
            'vision_icon' => ['nullable', 'string', 'max:50'],
            'vision_title' => ['nullable', 'string', 'max:255'],
            'vision_body' => ['nullable', 'string', 'max:1000'],
            'mission_icon' => ['nullable', 'string', 'max:50'],
            'mission_title' => ['nullable', 'string', 'max:255'],
            'mission_body' => ['nullable', 'string', 'max:1000'],
        ]);

        $data['content'] = match ($type) {
            'items' => collect($data['items'] ?? [])
                ->filter(fn ($item) => filled($item['title'] ?? null))
                ->map(fn ($item) => [
                    'icon' => $item['icon'] ?? null,
                    'title' => $item['title'],
                    'description' => $item['description'] ?? null,
                    'link' => $item['link'] ?? null,
                ])->values()->all(),
            'trust_badges' => collect($data['items'] ?? [])
                ->filter(fn ($item) => filled($item['title'] ?? null))
                ->map(fn ($item) => [
                    'icon' => $item['icon'] ?? null,
                    'title' => $item['title'],
                    'description' => $item['description'] ?? null,
                    'link' => $item['link'] ?? null,
                ])->values()->all(),
            'faq' => collect($data['items'] ?? [])
                ->filter(fn ($item) => filled($item['question'] ?? null))
                ->map(fn ($item) => [
                    'question' => $item['question'],
                    'answer' => $item['answer'] ?? null,
                    'category' => $item['category'] ?? null,
                ])->values()->all(),
            'process' => collect($data['items'] ?? [])
                ->filter(fn ($item) => filled($item['title'] ?? null))
                ->map(fn ($item) => [
                    'icon' => $item['icon'] ?? null,
                    'title' => $item['title'],
                    'description' => $item['description'] ?? null,
                ])->values()->all(),
            // All contact details (address/phone/email/whatsapp/hours/map
            // embed) now live in Settings > General — the single source of
            // truth shared by the footer, WhatsApp button, invoices, and
            // this page. This section is just a placeholder marking where
            // that block renders on the Contact page.
            'contact_info' => [],
            'testimonials' => [
                'limit' => (int) ($data['testimonial_limit'] ?? 6),
                'min_rating' => (int) ($data['testimonial_min_rating'] ?? 4),
            ],
            'fleet' => [
                'limit' => (int) ($data['fleet_limit'] ?? 12),
                'category_id' => $data['fleet_category_id'] ?? null,
            ],
            'routes' => [
                'limit' => (int) ($data['route_limit'] ?? 6),
            ],
            // The list of towns/cities itself is managed at Admin → Service
            // Areas, not per-section — this just marks where it renders.
            'areas' => [],
            'blog' => [
                'limit' => (int) ($data['blog_limit'] ?? 6),
            ],
            'stats' => collect($data['items'] ?? [])
                ->filter(fn ($item) => filled($item['label'] ?? null))
                ->map(fn ($item) => [
                    'icon' => $item['icon'] ?? null,
                    'label' => $item['label'],
                    'value' => (int) ($item['value'] ?? 0),
                    'suffix' => $item['suffix'] ?? null,
                ])->values()->all(),
            'vision_mission' => [
                'vision_icon' => $data['vision_icon'] ?? null,
                'vision_title' => $data['vision_title'] ?? null,
                'vision_body' => $data['vision_body'] ?? null,
                'mission_icon' => $data['mission_icon'] ?? null,
                'mission_title' => $data['mission_title'] ?? null,
                'mission_body' => $data['mission_body'] ?? null,
            ],
            'team' => collect($data['items'] ?? [])
                ->filter(fn ($item) => filled($item['name'] ?? null))
                ->map(function ($item) {
                    $photo = $item['existing_photo'] ?? null;

                    if (isset($item['photo']) && $item['photo'] instanceof \Illuminate\Http\UploadedFile) {
                        $photo = $this->storeUpload($item['photo'], $photo, 'team');
                    }

                    return [
                        'photo' => $photo,
                        'name' => $item['name'],
                        'role' => $item['role'] ?? null,
                        'bio' => $item['bio'] ?? null,
                    ];
                })->values()->all(),
            default => null,
        };

        unset(
            $data['items'],
            $data['testimonial_limit'], $data['testimonial_min_rating'], $data['fleet_limit'], $data['fleet_category_id'], $data['route_limit'], $data['blog_limit'],
            $data['vision_icon'], $data['vision_title'], $data['vision_body'],
            $data['mission_icon'], $data['mission_title'], $data['mission_body']
        );

        return $data;
    }

    private function storeUpload($file, ?string $previousFilename = null, string $directory = 'pages'): string
    {
        $path = public_path('uploads/'.$directory);

        if (! is_dir($path)) {
            mkdir($path, 0755, true);
        }

        $filename = 'section-'.time().'-'.Str::random(8).'.'.$file->getClientOriginalExtension();
        $file->move($path, $filename);

        $this->deleteUpload($previousFilename, $directory);

        return $filename;
    }

    private function deleteUpload(?string $filename, string $directory = 'pages'): void
    {
        if (! $filename) {
            return;
        }

        $path = public_path('uploads/'.$directory.DIRECTORY_SEPARATOR.$filename);

        if (file_exists($path)) {
            @unlink($path);
        }
    }
}
