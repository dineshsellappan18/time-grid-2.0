<?php

/**
 * Adapter so existing MailThief assertions keep working against MailHijacker.
 * lastMessage()->getBody('text') and ->subject map onto Swift_Mime_Message.
 */
class MailHijackerMessage
{
    /** @var \Swift_Mime_Message */
    protected $message;

    public function __construct(Swift_Mime_Message $message)
    {
        $this->message = $message;
    }

    public function __get($key)
    {
        if ($key === 'subject') {
            return $this->message->getSubject();
        }

        return null;
    }

    public function getBody($type = null)
    {
        return $this->message->getBody();
    }
}
