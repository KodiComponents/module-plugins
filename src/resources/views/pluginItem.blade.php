<script id="plugin-item" type="text/template">
	<td class="plugin-name" <% if (!isInstallable) { %>colspan="@can('plugin.change_status')) 3 @else 2 @endcan "<% } %>>
		<% if (!isInstallable) { %>
		<div class="alert alert-danger alert-dark padding-xs">
			<%= i18n.t('plugins.core.messages.not_installable', {
				required_version: required_cms_version,
				current_version: '{!! CMS::VERSION !!}'
		}) %>
		</div>
		<% } %>

		<% if (isActivated && settings_template) { %>
		@can('plugin.view_settings')

		<a href="<%= settingsUrl %>" class="btn btn-default btn-sm pull-right btn-labeled">
			<span class="hidden-xs hidden-sm btn-labeled" data-icon="cog">@lang('plugins::core.button.settings')</span>
		</a>

		@endcan
		<% } %>

		<h5 class="pull-left" <% if (icon) { %>data-icon="<%= icon %> fa-lg"<% } %>>
			<%= title %>
			<small>@lang('plugins::core.detail.version'): <strong><%= version %></strong></small>
		</h5>
		<div class="clearfix"></div>

		<% if (description) { %>
		<p class="text-muted"><%= description %></p>
		<% } %>

		<% if (author) { %>
		<small class="text-light-gray text-xs">
			@lang('plugins::core.detail.author'): <strong><%= author %></strong>
		</small>
		<% } %>
	</td>
	@can('plugin.change_status')
	<% if (isInstallable) { %>
	<td class="plugin-status text-center">
		{!! Form::button('', ['class' => 'change-status btn btn-default btn-sm']) !!}
	</td>
	<% } %>
	@endcan
</script>
