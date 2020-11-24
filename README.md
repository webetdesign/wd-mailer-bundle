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

class UserCreateEvent extends Event
{
    public const NAME = 'user.created';

    private $user;

    public function __construct($user)
    {
        $this->user = $user;
    }

    public function getUser()
    {
        return $this->user;
    }
    
    public function getEmail() {
        return 'toto@webetdesign.com';
    }
}
```
The use of the constant "NAME" allows to use an alias instead of the FQCN. 

Each getter will be used to determine the name of a variable in the template. In this example, there will be 2 variables, "user" and "email" that will be directly injected in the template.

## Listener
To use the events in the listener, you have to add the service with tags like this one
```yaml
services:
    WebEtDesign\MailerBundle\EventListener\MailerListener:
        tags:
            - { name: kernel.event_listener, event: user.created }
```
where "event" is the name of the event

## Evolution 

- Add multiple transport 
- Automatically add an event with interface or annotation
- Add attachment