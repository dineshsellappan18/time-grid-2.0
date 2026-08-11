<?php

namespace App\TG;

use Snowfire\Beautymail\Beautymail;

class TransMail
{
    protected ?Beautymail $mail = null;

    protected string $locale = 'en_US';

    protected string $localeSwitchFunction = 'setGlobalLocale';

    protected string $revertLocale = 'en_US';

    protected ?string $timezone = null;

    protected ?string $revertTimezone = null;

    protected string $subjectKey = '';

    protected array $subjectParams = [];

    protected string $viewBase = 'emails';

    protected string $viewPath = '';

    protected string $subject = '';

    protected bool $success = false;

    public function __construct(?Beautymail $mail = null)
    {
        $this->mail = $mail ?: app()->make(Beautymail::class);

        $this->locale();
    }

    public function useFunction(string $functionName): static
    {
        $this->localeSwitchFunction = $functionName;

        return $this;
    }

    public function locale(?string $posixLocale = null): static
    {
        $this->revertLocale = app()->getLocale();

        if ($posixLocale === null) {
            $posixLocale = $this->revertLocale;
        }

        $this->locale = $posixLocale;

        return $this;
    }

    public function timezone(?string $timezone): static
    {
        $this->revertTimezone = session()->get('timezone');

        $this->timezone = $timezone;

        return $this;
    }

    public function switchTimezone(?string $timezone): static
    {
        if ($timezone !== null && $timezone !== '') {
            $this->revertTimezone = session()->get('timezone');

            session()->set('timezone', $timezone);
            logger()->info("Switching timezone to $timezone for session");
        }

        return $this;
    }

    public function template(string $template): static
    {
        $this->viewPath = $template;

        return $this;
    }

    public function subject(string $key, array $params = []): static
    {
        $this->subjectKey = $key;

        $this->subjectParams = $params;

        return $this;
    }

    public function send(array $header, array $params): bool
    {
        $this->switchLocale($this->locale);
        $this->switchTimezone($this->timezone);

        $this->mail->send($this->getViewKey(), $params, function ($message) use ($header) {
            $message
                ->to(array_get($header, 'email'), array_get($header, 'name'))
                ->subject($this->getSubject());
        });

        $this->switchLocale($this->revertLocale);
        $this->switchTimezone($this->revertTimezone);

        $this->success = 0 == $this->mail->failures();

        return $this->success();
    }

    public function success(): bool
    {
        return $this->success;
    }

    protected function switchLocale(string $posixLocale): static
    {
        if (function_exists($this->localeSwitchFunction)) {
            call_user_func($this->localeSwitchFunction, $posixLocale);
        }

        return $this;
    }

    protected function getViewKey(): string
    {
        $key = $this->viewBase.'.'.$this->viewPath;

        if (!view()->exists($key)) {
            throw new \Exception('Email view does not exist: '.$key);
        }

        return $key;
    }

    protected function getSubject(): string
    {
        return $this->subject = trans('emails.'.$this->subjectKey, $this->subjectParams);
    }
}
