<?php

namespace Modules\Discord\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Factory;
use Modules\Discord\Entities\DiscordSettings;
use App\Misc;

define('SAMPLE_MODULE', 'discord');

class DiscordModuleServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Boot the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerTranslations();
        $this->registerConfig();
        $this->registerViews();
        $this->registerFactories();
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');

        \Eventy::addAction('mailboxes.settings.menu', function($mailbox) {
            if (auth()->user()->isAdmin()) {
                echo \View::make('discord::partials/settings_menu', ['mailbox' => $mailbox])->render();
            }
        }, 80);
        \Eventy::addAction('conversation.created_by_customer', function($conversation, $thread, $customer) {
            $settings = DiscordSettings::findOrFail($conversation->mailbox_id);

            if (!$settings->enabled || !$settings->webhook_url) {
                return;
            }

            $home = \Helper::urlHome();
            $conversation_url = $home . "/conversation/" . $conversation->id;

            $this->sendEmbed($settings->webhook_url, "New Support Ticket", $conversation_url, "A new support ticket has been created!", [
                [
                    "name" => "Sender Name",
                    "value" => $customer->getFullName(),
                    "inline" => true
                ],
                [
                    "name" => "Sender Address",
                    "value" => $customer->getMainEmail(),
                    "inline" => true
                ],
                [
                    "name" => "Subject",
                    "value" => $conversation->subject,
                    "inline" => false
                ],
                [
                    "name" => "Body",
                    "value" => $thread->getBodyAsText().substr(0, 500)
                ]
            ]);
        }, 20, 3);

        \Eventy::addAction('conversation.customer_replied', function($conversation, $thread, $customer) {
            $settings = DiscordSettings::findOrFail($conversation->mailbox_id);

            if (!$settings->enabled || !$settings->webhook_url) {
                return;
            }

            $home = \Helper::urlHome();
            $conversation_url = $home . "/conversation/" . $conversation->id;

            $this->sendEmbed($settings->webhook_url, "New Reply to Ticket", $conversation_url, "A new reply has been sent by the customer!", [
                [
                    "name" => "Sender Name",
                    "value" => $customer->getFullName(),
                    "inline" => true
                ],
                [
                    "name" => "Sender Address",
                    "value" => $customer->getMainEmail(),
                    "inline" => true
                ],
                [
                    "name" => "Subject",
                    "value" => $conversation->subject,
                    "inline" => false
                ],
                [
                    "name" => "Body",
                    "value" => $thread->getBodyAsText().substr(0, 500)
                ]
            ]);
        }, 20, 3);
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Register config.
     *
     * @return void
     */
    protected function registerConfig()
    {
        $this->publishes([
            __DIR__.'/../Config/config.php' => config_path('discord.php'),
        ], 'config');
        $this->mergeConfigFrom(
            __DIR__.'/../Config/config.php', 'discord'
        );
    }

    /**
     * Register views.
     *
     * @return void
     */
    public function registerViews()
    {
        $viewPath = resource_path('views/modules/discord');

        $sourcePath = __DIR__.'/../Resources/views';

        $this->publishes([
            $sourcePath => $viewPath
        ],'views');

        $this->loadViewsFrom(array_merge(array_map(function ($path) {
            return $path . '/modules/discord';
        }, \Config::get('view.paths')), [$sourcePath]), 'discord');
    }

    /**
     * Register translations.
     *
     * @return void
     */
    public function registerTranslations()
    {
        $langPath = resource_path('lang/modules/discord');

        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, 'discord');
        } else {
            $this->loadTranslationsFrom(__DIR__ .'/../Resources/lang', 'discord');
        }
    }

    /**
     * Register an additional directory of factories.
     * @source https://github.com/sebastiaanluca/laravel-resource-flow/blob/develop/src/Modules/ModuleServiceProvider.php#L66
     */
    public function registerFactories()
    {
        if (! app()->environment('production')) {
            app(Factory::class)->load(__DIR__ . '/../Database/factories');
        }
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [];
    }

    public function sendEmbed($webhook_url, $title, $url, $description, $fields) {
        $json_data = json_encode([
            "embeds" => [[
                "title" => $title,
                "url" => $url,
                "type" => "rich",
                "description" => $description,
                "timestamp" => date("c", strtotime("now")),
                "color" => hexdec("3366ff"),
                "fields" => $fields
            ]]
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        $ch = curl_init($webhook_url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: application/json'));
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, config('app.curl_timeout'));
        curl_setopt($ch, CURLOPT_PROXY, config('app.proxy'));

        curl_exec($ch);
        curl_close($ch);
    }
}
