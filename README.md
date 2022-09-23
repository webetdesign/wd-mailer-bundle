# webetdesign/wd-mailer-bundle

Bundle for add mailer management.

## Requirement
- PHP ^7.4
- symfony ^4

## Installation
Add the repo to your composer.json

```json
"repositories": [
	 {
	   "type": "git",
	   "url": "https://github.com/webetdesign/wd-mailer-bundle.git"
	 }
],
```

 And 

```
composer require webetdesign/wd-mailer-bundle
```

Register the bundles in `config/bundles.php`

``` php 
return [
    ...
    WebEtDesign\MailerBundle\WDMailerBundle::class => ['all' => true],
    Norzechowicz\AceEditorBundle\NorzechowiczAceEditorBundle::class => ['all' => true],
    ...
];
```

Register routes

```yaml 
# config/routes/wd_mailer.yaml
wd_mailer:
  resource: "@WDMailerBundle/Resources/config/routes.yaml"
```

configure your locales:
```yaml
# config/packages/wd_mailer.yaml
wd_mailer:
  locales: ['fr', 'en']
  default_locale: 'en'
```

config dependency bundle
```yaml
# config/packages/a2lix.yaml
a2lix_translation_form:
  locale_provider: default
  locales: '%wd_mailer.locales%'
  default_locale: '%wd_mailer.default_locale%'
  templating: "@A2lixTranslationForm/bootstrap_3_layout.html.twig"
```
```yaml
# config/packages/norzechowicz_ace_editor.yaml
norzechowicz_ace_editor:
  base_path: "http://rawgithub.com/ajaxorg/ace-builds/master"
```

## Mail

- **Name** : Name of the email (use only in BO)
- **Event** : Name of the event that will trigger this mail [voir: Event](#event)
- **To** : Allows to define the recipient of the mail (Use of variables possible)
- **From** : Indicates who the mail comes from
- **Content HTML** : Content of the mail

For use variables in "to" field set a value like this :
 ```
 __user__
 ```

## Event
Sending an email is based on the triggering of an event.

Sample event

```php
<?php

namespace App\Event;

use Symfony\Contracts\EventDispatcher\Event;
use WebEtDesign\MailerBundle\Event\AbstractMailEvent;use WebEtDesign\MailerBundle\Event\MailEventInterface;
use WebEtDesign\MailerBundle\Attribute\MailEvent;
use Symfony\Component\HttpFoundation\File\File;

#[MailEvent(name: self::USER_CREATED, label: 'Utlisateur créé')]
class UserCreateEvent extends AbstractMailEvent
{
    public const USER_CREATED = 'USER_CREATED';

    private $user;

    public function __construct($user)
    {
        $this->user = $user;
    }

    public function getUser()
    {
        return $this->user;
    }
    
    public function getEmail(): string 
    {
        return 'toto@webetdesign.com';
    }
    
    public function getFile(): ?File
    {
        return null;
    }
}
```
Each getter will be used to determine the name of a variable in the template. In this example, there will be 2 variables, "user" and "email" that will be directly injected in the template.

## Dispatch event

```php 
$eventDispatcher->dispatch(new UserCreateEvent($this->getUser()), UserCreateEvent::USER_CREATED);
```

## Evolution 

- Add multiple transport 
