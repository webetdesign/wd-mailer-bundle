<?php
declare(strict_types=1);

namespace WebEtDesign\MailerBundle\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
class MailEvent
{
    public function __construct(
        public string            $name,
        public string            $label,
        public bool              $spool = false,
        public int               $priority = 0,
        public string|array|null $subject = null,
        public ?string           $templateHtml = null,
        public ?string           $templateText = null,
    )
    {
    }
}
