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
        public ?CategoryEnum     $category = null,
        public array             $documents = [],
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
            'documents'    => $this->documents,
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
            name: $settings['name'],
            label: $settings['label'],
            category: !empty($settings['category']) ? CategoryEnum::tryFrom($settings['category']) : null,
            documents: $settings['documents'] ?? [],
            spool: $settings['spool'] ?? false,
            priority: $settings['priority'] ?? 0,
            subject: $settings['subject'] ?? null,
            templateHtml: $settings['templateHtml'] ?? null,
            templateText: $settings['templateText'] ?? null,
        );
    }
}
