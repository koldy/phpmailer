<?php declare(strict_types=1);

namespace KoldyPHPMailer;

use Closure;
use Koldy\Mail\Adapter\AbstractMailAdapter;
use PHPMailer\PHPMailer\Exception as PHPMailerException;
use PHPMailer\PHPMailer\PHPMailer as NativePHPMailer;
use Throwable;

/**
 * This is only driver class that uses PHPMailer. You need to set the include path the way that PHP can include it. We recommend that you set that path
 * in config/application.php under additional_include_path. Path defined there must be the path where class.phpmailer.php is located.
 *
 * @link http://koldy.net/docs/mail/phpmailer
 */
class PHPMailer extends AbstractMailAdapter
{

    private NativePHPMailer $mailer;

    /**
     * Construct the object
     *
     * @param array $config
     *
     * @throws Exception
     */
    public function __construct(array $config)
    {
        parent::__construct($config);

        $this->mailer = new NativePHPMailer(true);
        $this->mailer->CharSet = $config['charset'] ?? 'UTF-8';
        $this->mailer->Host = $config['host'];
        $this->mailer->Port = $config['port'];

        if (isset($config['username'])) {
            $this->mailer->Username = $config['username'];
        }

        if (isset($config['password'])) {
            $this->mailer->Password = $config['password'];
        }

        switch ($config['type']) {
            default:
            case 'smtp':
                $this->mailer->isSMTP();

                if (isset($config['username']) && isset($config['password'])) {
                    $this->mailer->SMTPAuth = true;
                }

                break;

            case 'mail':
                $this->mailer->isMail();
                break;
        }

        if (isset($config['adjust'])) {
            // it's set, let's validate

            $adjustFn = $config['adjust'];

            if (!($adjustFn instanceof Closure)) {
                throw new Exception('Invalid configuration for \'adjust\' - this property must be function that accepts PHPMailer instance as parameter');
            }

            call_user_func($adjustFn, $this->mailer);
        }
    }

	/**
	 * Set email's "from"
	 *
	 * @param string $email
	 * @param string|null $name
	 *
	 * @return $this
	 * @throws PHPMailerException
	 */
    public function from(string $email, string $name = null): static
    {
        $this->mailer->setFrom($email, $name ?? '');
        return $this;
    }

	/**
	 * Set email's "Reply To" option
	 *
	 * @param string $email
	 * @param string|null $name
	 *
	 * @return $this
	 * @throws PHPMailerException
	 */
    public function replyTo(string $email, string $name = null): static
    {
        $this->mailer->addReplyTo($email, $name ?? '');
        return $this;
    }

	/**
	 * Set email's "to"
	 *
	 * @param string $email
	 * @param string|null $name
	 *
	 * @return $this
	 * @throws PHPMailerException
	 */
    public function to(string $email, string $name = null): static
    {
        $this->mailer->addAddress($email, $name ?? '');
        return $this;
    }

	/**
	 * Send mail carbon copy
	 *
	 * @param string $email
	 * @param string|null $name
	 *
	 * @return $this
	 * @throws PHPMailerException
	 * @link http://koldy.net/docs/mail#example
	 */
    public function cc(string $email, string $name = null): static
    {
        $this->mailer->addCC($email, $name ?? '');
        return $this;
    }

	/**
	 * Send mail blind carbon copy
	 *
	 * @param string $email
	 * @param string|null $name
	 *
	 * @return $this
	 * @throws PHPMailerException
	 * @link http://koldy.net/docs/mail#example
	 */
    public function bcc(string $email, string $name = null): static
    {
        $this->mailer->addBCC($email, $name ?? '');
        return $this;
    }

    /**
     * Set email's subject
     *
     * @param string $subject
     *
     * @return $this
     */
    public function subject(string $subject): static
    {
        $this->mailer->Subject = $subject;
        return $this;
    }

    /**
     * @param string $body
     * @param bool $isHTML
     * @param string|null $alternativeText
     *
     * @return $this
     */
    public function body(string $body, bool $isHTML = false, string $alternativeText = null): static
    {
        $this->mailer->Body = $body;

        if ($isHTML) {
            $this->mailer->isHTML();
        }

        if ($alternativeText !== null) {
            $this->mailer->AltBody = $alternativeText;
        }

        return $this;
    }

	/**
	 * @param string $fullFilePath
	 * @param string|null $attachedAsName
	 *
	 * @return $this
	 * @throws PHPMailerException
	 */
    public function attachFile(string $fullFilePath, string $attachedAsName = null): static
    {
        $this->mailer->addAttachment($fullFilePath, $attachedAsName ?? '');
        return $this;
    }

    /**
     * @throws Exception
     */
    public function send(): void
    {
        try {

            if (!$this->mailer->send()) {
                throw new Exception($this->mailer->ErrorInfo);
            }

        } catch (PHPMailerException | Throwable $e) {
            throw new Exception($e->getMessage(), $e->getCode(), $e);

        }
    }

    /**
     * Get the PHP mailer instance for fine tuning, but try not to rely on this method
     *
     * @return NativePHPMailer
     */
    public function getPHPMailer(): NativePHPMailer
    {
        return $this->mailer;
    }

}
