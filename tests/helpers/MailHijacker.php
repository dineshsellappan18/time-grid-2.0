<?php

/**
 * Transitional mail capture for Laravel 5.3 acceptance tests (WO-003).
 * Replaces tightenco/mailthief until Mail::fake is available (Laravel ≥5.4 / hop path).
 */
class MailHijacker
{
    /** @var \Swift_Plugins_MessageLogger */
    protected static $logger;

    public static function hijack()
    {
        self::$logger = new Swift_Plugins_MessageLogger();
        app('mailer')->getSwiftMailer()->registerPlugin(self::$logger);
        // Prevent real SMTP while still exercising the mailer pipeline.
        config(['mail.driver' => 'log']);
    }

    public static function hasMessageFor($email)
    {
        foreach (self::messages() as $message) {
            if (array_key_exists($email, (array) $message->getTo())
                || array_key_exists($email, (array) $message->getCc())
                || array_key_exists($email, (array) $message->getBcc())) {
                return true;
            }
        }

        return false;
    }

    public static function lastMessage()
    {
        $messages = self::messages();
        $last = end($messages);

        return $last ? new MailHijackerMessage($last) : null;
    }

    /**
     * @return \Swift_Mime_Message[]
     */
    public static function messages()
    {
        if (self::$logger === null) {
            return [];
        }

        return self::$logger->getMessages();
    }
}
