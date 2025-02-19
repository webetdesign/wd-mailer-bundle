<?php
declare(strict_types=1);

namespace WebEtDesign\MailerBundle\Attribute;

use Attribute;
use WebEtDesign\MailerBundle\Enum\CategoryEnum;

#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
class MailEvent
{
    public function __construct(
        public string            $name,
        public string            $label,
        public ?CategoryEnum $category = null,
        public bool              $spool = false,
        public int               $priority = 0,
        public string|array|null $subject = null,
        public ?string           $templateHtml = null,
        public ?string           $templateText = null,
    )
    {
    }

    public function toJson(): false|string
    {
        return json_encode([
            'name'         => $this->name,
            'label'        => $this->label,
            'category'     => $this->category?->value,
            'spool'        => $this->spool,
            'priority'     => $this->priority,
            'subject'      => $this->subject,
            'templateHtml' => $this->templateHtml,
            'templateText' => $this->templateText,
        ]);
    }

    public static function createFromJson(string $json): self
    {
        $settings = json_decode($json, true);

        return new self(
            $settings['name'],
            $settings['label'],
            !empty($settings['category']) ? CategoryEnum::tryFrom($settings['category']) : null,
            $settings['spool'] ?? false,
            $settings['priority'] ?? 0,
            $settings['subject'] ?? null,
            $settings['templateHtml'] ?? null,
            $settings['templateText'] ?? null,
        );
    }
}
