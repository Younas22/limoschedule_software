<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PageSection extends Model
{
    public const TYPES = [
        'hero' => 'Hero Banner',
        'rich_text' => 'Rich Text',
        'items' => 'Item Grid (features / services / team / stats)',
        'faq' => 'FAQ List',
        'contact_info' => 'Contact Info',
        'cta' => 'Call To Action',
        'testimonials' => 'Testimonials (Live Reviews)',
        'fleet' => 'Fleet Showcase (Live Vehicles)',
        'routes' => 'Popular Routes (Live Routes)',
        'stats' => 'Statistics Counters',
        'blog' => 'Blog Highlights (Live Posts)',
        'vision_mission' => 'Vision & Mission',
        'team' => 'Team Members',
    ];

    protected $fillable = [
        'page_id',
        'type',
        'heading',
        'subheading',
        'body',
        'image',
        'video',
        'button_text',
        'button_url',
        'button_text_2',
        'button_url_2',
        'content',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'content' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->type] ?? ucfirst(str_replace('_', ' ', $this->type));
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->image ? asset('public/uploads/pages/'.$this->image) : null;
    }

    public function getVideoUrlAttribute(): ?string
    {
        return $this->video ? asset('public/uploads/pages/'.$this->video) : null;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getItemsAttribute(): array
    {
        return $this->type === 'items' ? ($this->content ?? []) : [];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getFaqItemsAttribute(): array
    {
        return $this->type === 'faq' ? ($this->content ?? []) : [];
    }

    /**
     * @return array<string, mixed>
     */
    public function getContactFieldsAttribute(): array
    {
        return $this->type === 'contact_info' ? ($this->content ?? []) : [];
    }

    /**
     * @return array{limit: int, min_rating: int}
     */
    public function getTestimonialSettingsAttribute(): array
    {
        $content = $this->type === 'testimonials' ? ($this->content ?? []) : [];

        return [
            'limit' => (int) ($content['limit'] ?? 6),
            'min_rating' => (int) ($content['min_rating'] ?? 4),
        ];
    }

    /**
     * @return array{limit: int, category_id: ?int}
     */
    public function getFleetSettingsAttribute(): array
    {
        $content = $this->type === 'fleet' ? ($this->content ?? []) : [];

        return [
            'limit' => (int) ($content['limit'] ?? 12),
            'category_id' => isset($content['category_id']) ? (int) $content['category_id'] : null,
        ];
    }

    /**
     * @return array{limit: int}
     */
    public function getRouteSettingsAttribute(): array
    {
        $content = $this->type === 'routes' ? ($this->content ?? []) : [];

        return [
            'limit' => (int) ($content['limit'] ?? 6),
        ];
    }

    /**
     * @return array<int, array{icon: ?string, label: string, value: int, suffix: ?string}>
     */
    public function getStatsAttribute(): array
    {
        return $this->type === 'stats' ? ($this->content ?? []) : [];
    }

    /**
     * @return array{limit: int}
     */
    public function getBlogSettingsAttribute(): array
    {
        $content = $this->type === 'blog' ? ($this->content ?? []) : [];

        return [
            'limit' => (int) ($content['limit'] ?? 6),
        ];
    }

    /**
     * @return array{vision_icon: ?string, vision_title: ?string, vision_body: ?string, mission_icon: ?string, mission_title: ?string, mission_body: ?string}
     */
    public function getVisionMissionAttribute(): array
    {
        $content = $this->type === 'vision_mission' ? ($this->content ?? []) : [];

        return [
            'vision_icon' => $content['vision_icon'] ?? 'eye',
            'vision_title' => $content['vision_title'] ?? 'Our Vision',
            'vision_body' => $content['vision_body'] ?? null,
            'mission_icon' => $content['mission_icon'] ?? 'trending-up',
            'mission_title' => $content['mission_title'] ?? 'Our Mission',
            'mission_body' => $content['mission_body'] ?? null,
        ];
    }

    /**
     * @return array<int, array{photo: ?string, name: string, role: ?string, bio: ?string}>
     */
    public function getTeamMembersAttribute(): array
    {
        return $this->type === 'team' ? ($this->content ?? []) : [];
    }
}
