<?php

namespace BoxedCode\Laravel\Auth\Device\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\HtmlString;

class AuthorizationRequest extends Notification
{
    use Queueable;

    /**
     * The verify token.
     *
     * @var string
     */
    public $verifyToken;

    /**
     * The IP address the notification was request from.
     *
     * @var string
     */
    public $ip;

    /**
     * The browser name the notification was requested from.
     *
     * @var string
     */
    public $browser;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($verifyToken, $browser, $ip)
    {
        $this->verifyToken = $verifyToken;

        $this->ip = $ip;

        $this->browser = $browser;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     *
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     *
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage())
            ->subject('New Device Confirmation')
            ->line('You recently attempted to sign into your account from a new device. As a security measure, we require additional confirmation before allowing access to your account:')
            ->line(new HtmlString('<strong>IP Address: '.$this->ip.'</strong>'))
            ->line(new HtmlString('<strong>Browser: '.$this->browser.'</strong>'))
            ->line('Note thate you will need to do this on the same device and in the same browser as you were using.')
            ->action('Verify Device', route('device.verify', [$this->verifyToken]))
            ->line('Thanks for helping us to keep your account secure!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param mixed $notifiable
     *
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'message' => 'You requested to authenticate a new device.',
            'url'     => route('device.verify', [$this->verifyToken]),
            'ip'      => $this->ip,
            'browser' => $this->browser,
        ];
    }
}
