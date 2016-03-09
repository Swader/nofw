## NOFW skeleton

A lightweight (relatively, compared to modern frameworks) no-framework skeleton app with login / registration functionality. Useful for framework-agnostic tutorials. It includes the following out of the box:

- Routing via ["nikic/fast-route"](https://github.com/nikic/FastRoute) - see `app/routes.php`
- Dependency Injection via ["php-di/php-di"](https://github.com/PHP-DI/PHP-DI) - see `app/config.php`
- View (template) engine via ["twig/twig"](http://twig.sensiolabs.org/)
- Sign Up / Log in functionality via ["psecio/gatekeeper"](https://github.com/psecio/gatekeeper) (see `.env` for MySQL credentials and `phinx.yml` for migration info) - currently hard-coupled into app
- Password reset email via Mailgun and Guzzle (see `.env` for where to put Mailgun key and domain and see `Views/emails/forgotpass.twig` for email template)
- Flash messages via ["tamtamchik/simple-flash"](https://github.com/tamtamchik/simple-flash), see master layout for where they're displayed, and `config.php` for where they are passed into Twig if they exist. In `config.php` you can also define a custom pre-made style to the templates - many popular CSS frameworks are supported. Defaults to Foundation.
- Validation with ["respect/validation"](https://github.com/Respect/Validation) (usage example in AuthController - currently hard-coupled to app)
- Annotation-based ACL (for controlling access to classes and methods, not routes) via [SitePoint/Rauth](https://github.com/sitepoint/Rauth)
- Automatic image resizing for media queries via [league/glide](http://glide.thephpleague.com) - see below for explanation

Additionally, the project includes support for an optional front-end workflow without NodeJS and NPM: full build chains and file watchers included. For more information about this approach, see [FRONTEND.md](docs/FRONTEND.md).

## Prerequisites

- MySQL (due to Gatekeeper)
- PHP 7+ (due to sanity)
- a decent development environment. [Homestead Improved](http://www.sitepoint.com/quick-tip-get-homestead-vagrant-vm-running/) will do just fine.

## Installation

```bash
git clone https://github.com/swader/nofw myproject
cd myproject
composer install
```

Then, open `data/db/gatekeeper_init.sql` and change the username, password, and database name if needed. Then run the SQL script by either pasting it into a MySQL management tool, or directly from the command line:

```bash
mysql -u myuser -p < data/db/gatekeeper_init.sql
```

Finally, finish Gatekeeper installation by running:

```bash
vendor/bin/setup.sh
```

Enter all the required data, and make sure it matches the data just entered via the SQL script.

That's it. Point the server at `public/index.php` and enjoy.

## First user

To create the first user, who will automatically be given the "admin" group, follow the instructions and just register a regular account. Every subsequent user will be given the regular "users" group.

To limit access to certain classes or methods, see [SitePoint/Rauth](https://github.com/sitepoint/Rauth) for documentation, and AccountController for example usage.

## Features and Fine Tuning

This is a skeleton project, and as such it is made to be tweaked and extended.

### Routing

Add more routes in `app/routes.php`.

Unlike traditional access control lists, this project uses [SitePoint/Rauth](https://github.com/sitepoint/Rauth) which lets you limit access to specific classes and/or methods via annotations on those classes and/or methods. The process is automatic with controllers - it's configured in `index.php` before the dispatcher calls the requested class::method pair. For manual control of all other classes, just grab the Rauth instance out of the container with `$container->get('rauth')` and then call `authorize` on that instance, as per [Rauth docs](https://github.com/sitepoint/Rauth).

### Gatekeeper

See the [GK docs](http://gatekeeper-auth.readthedocs.org/) to learn about it. Basically, the logged in user is always accessible via the container, by calling `$container->get('User')`. If there is no logged in user, `null` is returned.

Even though Gatekeeper supports group and permission hierarchy, it is recommended they be used as basic flags. So if someone is an "admin", it is not to be assumed they are also a "moderator" - they should have both the "moderators" and "admin" group in order to see all the actions available to both groups. When the first user account is created, two groups are also automatically created if they don't yet exist: "admin" and "users".

### Services

All services are built in `app/config.php` as part of the PHP-DI dependency injection container. Whenever you feel like calling the `new` keyword in a controller or another service, reconsider and do it in `app/config.php` instead. This has several advantages, not the least of which are the fact that all services are registered in one place and easily tracked, and the fact that your application is not tightly coupled to those services - you can replace their implementation with something else in the configuration later on, and as long as the API is the same, the app needn't know about the change.

### Flash Messages

To get access to flash messages, either retrieve the flasher instance from the container (`$container->get(Tamtamchik\SimpleFlash\Flash::class)`) or have it auto-injected into controllers (see `AuthController` for example and [PHP-DI docs](http://php-di.org/doc/best-practices.html#writing-controllers) for documentation about this).

To style the messages, several pre-configured templates exist that you can inject into the Flash class. Most popular CSS frameworks are covered. For available templates, see `vendor/tamtamchik/simple-flash/src/Templates/`. Change the injected template in `app/config.php` (notice that Foundation6 is injected by default).

### Image generation

This skeleton comes with [Glide](http://glide.thephpleague.com) which generates resized images from a source image on-demand - perfect for media queries. It also saves them for later, so the next time they're requested, they don't need to be regenerated. In a nutshell, this allows you to have a single image like `assets/image/xyz.png`, and then request it with `/static/image/xyz-WIDTH.png` and it will get automatically generated at that width.

For a demonstration of this, see the homepage when you install the project, or read [this tutorial](http://www.sitepoint.com/easy-dynamic-on-demand-image-resizing-with-glide).

Note that while this is on by default, it is entirely optional - you can disable this image generation by commenting out the related route in `routes.php`.

### Styling

See [FRONTEND.md](docs/FRONTEND.md).

### Error pages

When a route is not found, a 404 page is automatically rendered. When the wrong verb is used on a route (like `GET` instead of `POST` on a `processSignup` method), a 500 error is rendered. Both of these templates are in `src/Standard/Views`.

### Sending Emails

Sending "forgot password" emails is done via Mailgun, purely because it's simplest. The email templates are in `src/Standard/Views/emails`. However, it should be noted that it's currently used without a Mailgun client package or any other dependency, so really, the emailing system is completely optional and interchangeable. If, however, you do choose to use Mailgun, put the credentials into the `.env` file (see `.env.example` for inspiration) and then see how it was done in `AuthController::forgotPassword`.

### Debug mode

To switch debug mode on/off, change it in `.env`. See `.env.example` for inspiration. Currently debug mode only affects the output of permissions errors via Rauth (see `index.php`).

### Extending

When extending the application with your own services and controllers, it is recommended you use a different namespace than the `Standard` included by default. `Standard` is there for the basic skeleton stuff, and will be improved further with time. To write your own controllers, classes, etc, make a new folder in `src`, e.g. `MyNamespace`, and then put your stuff in there. Don't forget to register this new namespace in `composer.json` and regenerate autoload files by running:

```bash
composer dump-autoload
```

