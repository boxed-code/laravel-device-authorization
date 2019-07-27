<?php

namespace BoxedCode\Laravel\Auth\Device\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\HtmlString;

class AuthorizationRequest extends Notification
{
    use Queueable;

    protected $token;

    protected $ip;

    protected $browser;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($token, $ip, $browser)
    {
        $this->token = $token;

        $this->ip = $ip;

        $this->browser = $browser;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('New Device Confirmation')
            ->line('You recently attempted to sign into your account from a new device. As a security measure, we require additional confirmation before allowing access to your account:')
            ->line(new HtmlString('<strong>IP Address: ' . $this->ip . '</strong>'))
            ->line(new HtmlString('<strong>Browser: ' . $this->browser . '</strong>'))
            ->line('Note thate you will need to do this on the same device and in the same browser as you were using.')
            ->action('Verify Device', route('device.verify', [$this->token]))
            ->line('Thanks for helping us to keep your account secure!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'message' => 'You requested to authenticate a new device.',
            'url' => route('device.verify', [$this->token]),
            'ip' => $this->ip,
            'browser' => $this->browser,
        ];
    }
}