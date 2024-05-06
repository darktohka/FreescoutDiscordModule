<li @if (Route::is('discord.settings'))class="active"@endif><a href="{{ route('discord.settings', ['mailbox_id'=>$mailbox->id]) }}"><i class="glyphicon glyphicon-envelope"></i>Discord</a></li>
