@include('plugins::pluginItem')

<div id="pluginsMap" class="panel">
	<table class="table table-primary table-striped table-hover" id="PluginsList">
		<colgroup>
			<col />
			@can('plugins::change_status')
			<col width="100px" />
			@endcan
		</colgroup>
		<thead>
		<tr>
			<th>@lang('plugins::core.field.title')</th>
			@can('plugins::change_status')
			<th>@lang('plugins::core.field.actions')</th>
			@endcan
		</tr>
		</thead>
		<tbody></tbody>
	</table>
</div>