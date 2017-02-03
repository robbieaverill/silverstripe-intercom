# SilverStripe Intercom Module

[![Build Status](https://travis-ci.org/silverstripe/silverstripe-intercom.svg?branch=master)](https://travis-ci.org/silverstripe/silverstripe-intercom)

This module provides SilverStripe integration for [Intercom](https://www.intercom.io/).

## Requirements
 * SilverStripe Framework 4.0 or higher (3.x support in `1.x` branches and tags). Works nicely with the CMS but it isn't required.
 * PHP >=5.6.0

## Installation
Install the module with composer, just like all your favourite modules!

```
composer require silverstripe/silverstripe-intercom
```

## License
See [License](license.md)

## How it works
Intercom's integration is quite simple: a few lines of JavaScript added just before the `</body>` tag.
The module adds through its own RequestFilter.

## Configuration

The module will make use of the following global constants in your `.env` file. You should 
set these up:

 * `INTERCOM_APP_ID`: The "App ID" from Intercom's integration settings. Required.
 * `INTERCOM_PERSONAL_ACCESS_TOKEN`: The "Personal Access Token" from Intercom's integration settings. Required.
 * `INTERCOM_SECRET_KEY`: The secret key given by Intercom's Secure Mode. Optional, but highly recommended.

Note that if you disclose your secret key to anyone, they could impersonate users of your app and chat to 
your support team, so keep it secure! The App ID is less sensitive as it is in the HTML source of your 
site.

I recommend that you enable the "Test Version" of Intercom. This will give you a second App ID that
you should use on your test and development environments.

Your application can customise the information send with the following properties

```yaml
SilverStripe\Intercom\IntercomScriptTags:
  anonymous_access: true
  company_property: Organisation
  company_fields:
    name: Title
    plan: AccountPlan
  user_fields:
    favourite_colour: FavouriteColour
```

 * `anonymous_access`: If true, then the integration code will be supplied even for anonmyous visitors.
   This is used for Intercom Acquire.
 * `company_property`: The property on a member that points to their organisation
 * `company_fields`: A map of Intercom field name to organisation properties to pass through
 * `user_fields`: A map of Intercom field name to member properties to pass through


### Only show Intercom sometimes

The `SilverStripe\Intercom\IntercomScriptTags` class has a configuration value, `enabled`, that
can will disable any inclusion of Intercom script tags if set to false.

If you wish to show Intercom only sometimes, you can update this configuration value at any time during
your page load, with the following command. For example, you may choose to show Intercom only on certain
pages, or for certain users.

```php
\SilverStripe\Intercom\IntercomScriptTags::config()->enabled = false;
```

## Usage

### Tracking events

You can track events with the `Intercom::trackEvent()` method. The event will be tracked against the
current user.

```php
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Intercom\Intercom;

$intercom = Injector::inst()->get(Intercom::class);
$intercom->trackEvent('test-event', array(
    'something' => 'a value',
    'other-one' => 'moar data',
));
```

You can also explicitly specify which user this event should be tracked against:

```php
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Intercom\Intercom;
use SilverStripe\Security\Member;

$member = Member::get()->byID(34);
$intercom = Injector::inst()->get(Intercom::class);
$intercom->trackEvent('test-event', array(
    'something' => 'a value',
    'other-one' => 'moar data',
), $member);
```

Note that you can't currently track events for anonymous visitors; a LogicException will be thrown if you 
try.

### Synchronising users via the API

Sometimes, it's not enough to wait until users log in to have their Intercom data updated. For example, if
you want to use Intercom to send emails you may want to update the Intercom database before the first email
is sent.

For this purpose, you can set `dev/tasks/IntercomBulkLoadTask` to run on a cronjob. By default it will
synchronise all Member objects. If you wish to synchornise a reduced list of Members, you can set the
`user_list` config option on the Intercom class. This should be of the form `%$ServiceName`, where ServiceName
is the name of an Injector service.

```yaml
SilverStripe\Intercom\Intercom:
  user_list: %$AllPlatformUsers
SilverStripe\Core\Injector\Injector:
  AllPlatformUsers:
    factory: AllUserListFactory
```

In the preceding example, we're using a custom factory class called `AllUserListFactory` to define Member DataList.
It needs to have a method called `create()` that returns a `DataList` of `Member`s.

```php
use SilverStripe\Security\Member;

/**
 * Factory for generating a DataList of all platform users
 */
class AllUserListFactory
{
    public function create($class, $params)
    {
        return Member::get()->filter(['Some' => 'Value']);
    }
}
```

### Integration with forms

To send data to Intercom from a form, you can use `SilverStripe\Intercom\IntercomFormExtension`.

```yaml
SilverStripe\Forms\Form:
  extensions:
    - SilverStripe\Intercom\IntercomFormExtension
```

This will provide several chainable methods to the `Form` class that help you map form fields to Intercom fields.

```php
$form
    ->addIntercomUserFieldMapping([
		'FullName' => 'name',
		'EmailAddress' => 'email'
	])
	->addIntercomCompanyFieldMapping([
		'CompanyName' => 'name'
	])
	->sendToIntercom();
```

For custom attributes, prefix the field name with `$`.

```php
$form->addIntercomUserFieldMapping([
	'FavouriteColour' => '$favourite_color'
]);
```

Additionally, you can stuff assorted fields into a monolithic "note" for the user in Intercom. This map is keyed with labels that should precede the values for each field.

```php
$form
    ->addIntercomNoteMapping([
		'SoftwareVersion' => 'The user is running version:'
	])
	->setIntercomNoteHeader('More information about this user');
```

The above will create a note similar to:

```html
<p>More information about this user</p>
<ul>
	<li>The user is running version: ${SoftwareVersion}</li>
</ul>
```

#### Integration with Userforms

See the [silverstripe-intercom-userforms](https://github.com/unclecheese/silverstripe-intercom-userforms) module by Uncle Cheese.

## Maintainers
 
 * Sam Minnée <sam@silverstripe.com>
 
## Bugtracker

Bugs are tracked in the [issues section](https://github.com/silverstripe/silverstripe-intercom/issues) of this
repository. Before submitting an issue please read over existing issues to ensure yours is unique.
 
If the issue does look like a new bug:
 
 - Create a new issue
 - Describe the steps required to reproduce your issue, and the expected outcome. Unit tests, screenshots 
 and screencasts can help here.
 - Describe your environment as detailed as possible: SilverStripe version, Browser, PHP version, 
 Operating System, any installed SilverStripe modules.
 
Please report security issues to the module maintainers directly. Please don't file security issues in the bugtracker.
 
## Development and contribution

If you would like to make contributions to the module please ensure you raise a pull request and discuss with the module maintainers.
