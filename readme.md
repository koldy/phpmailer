# Koldy PHPMailer

Use this package to integrate PHPMailer with your project built on top of Koldy Framework. Minimum PHP version is PHP `8.1`.

```shell
composer require koldy/phpmailer
```

## Configuration

In your project's config folder (usually `configs/mail.php`), add the following block:

```phpt
'phpmailer' => [
    'enabled' => true,
    'adapter_class' => \KoldyPHPMailer\PHPMailer::class,
    'options' => [
        'host' => 'your.domain.com',
        'port' => 587,
        'username' => 'your.username',
        'password' => 'your.password',
        'type' => 'smtp',
        'adjust' => function ($phpmailer) { // optional
            // you can adjust the PHPMailer's instance here, for example:
            $phpmailer->SMTPDebug = \PHPMailer\PHPMailer\SMTP::DEBUG_SERVER;
        }
    ]
]
```

## Licence

Open sourced and published under [MIT licence](http://opensource.org/licenses/MIT).
