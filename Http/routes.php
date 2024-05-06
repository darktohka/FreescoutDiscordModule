<?php

Route::group(['middleware' => 'web', 'prefix' => \Helper::getSubdirectory(), 'namespace' => 'Modules\Discord\Http\Controllers'], function()
{
    Route::get('/mailbox/discord-settings/{mailbox_id}', ['uses' => 'DiscordModuleController@settings', 'middleware' => ['auth', 'roles'], 'roles' => ['admin']])->name('discord.settings');
    Route::post('/mailbox/discord-settings/{mailbox_id}', ['uses' => 'DiscordModuleController@saveSettings', 'middleware' => ['auth', 'roles'], 'roles' => ['admin']]);
});
