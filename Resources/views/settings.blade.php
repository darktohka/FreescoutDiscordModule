@extends('layouts.app')

@section('title_full', 'Discord - ' . $mailbox->name)

@section('body_attrs')@parent data-mailbox_id="{{ $mailbox->id }}"@endsection

@section('sidebar')
    @include('partials/sidebar_menu_toggle')
    @include('mailboxes/sidebar_menu')
@endsection

@section('content')
    <div class="section-heading">
        Discord
    </div>
    <div class="col-xs-12">
        <form class="form-horizontal margin-top margin-bottom" method="POST" action="">
            {{ csrf_field() }}

            <div class="form-group">
                <label for="enabled" class="col-sm-2 control-label">{{ __("Enable Discord notifications") }}</label>

                <div class="col-sm-6">
                    <div class="controls">
                        <div class="onoffswitch-wrap">
                            <div class="onoffswitch">
                                <input type="checkbox" name="enabled" id="enabled" class="onoffswitch-checkbox"
                                    {!! $settings['enabled'] ? "checked" : "" !!}
                                >
                                <label class="onoffswitch-label" for="enabled"></label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label class="col-sm-2 control-label">{{ __("Discord webhook URL") }}</label>

                <div class="col-sm-6">
                    <input name="webhook_url" class="form-control" placeholder="https://discord.com/api/webhooks/..." value="{{ $settings['webhook_url'] }}" required />
                </div>
            </div>

            <div class="form-group margin-top margin-bottom">
                <div class="col-sm-6 col-sm-offset-2">
                    <button type="submit" class="btn btn-primary">
                        {{ __("Save") }}
                    </button>
                </div>
            </div>
        </form>
    </div>
@endsection

@section('body_bottom')
    @parent
@endsection
