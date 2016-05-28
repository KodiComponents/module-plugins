@include('plugins::pluginItem')

<div id="pluginsMap" class="panel">
    <table v-if="plugins.length" class="table table-primary table-striped table-hover" id="PluginsList">
        <colgroup>
            <col/>
            @can('plugins::change_status')
                <col width="100px"/>
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
        <tbody>
            <tr v-for="plugin in plugins | orderBy 'isActivated'">
                <td class="plugin-name">
                    <div v-if="!plugin.isInstallable" class="alert alert-danger alert-dark padding-xs">
                        @{{ trans('plugins.core.messages.not_installable', {
                            required_version: plugin.required_cms_version,
                            current_version: '{!! CMS::getVersion() !!}'
                        }) }}
                    </div>

                    @can('plugins::view_settings')
                    <a v-if="plugin.isActivated && plugin.settings_template" href="@{{ plugin.settingsUrl }}" class="btn btn-default btn-sm pull-right btn-labeled">
                        <span class="hidden-xs hidden-sm btn-labeled" data-icon="cog">
                            @lang('plugins::core.button.settings')
                        </span>
                    </a>
                    @endcan

                    <h4 class="pull-left">
                        <i v-if="plugin.icon" class="fa fa-@{{ plugin.icon }} fa-2x"></i>
                        @{{{ plugin.title}}}

                        <small>@lang('plugins::core.detail.version'): <strong>@{{ plugin.version }}</strong></small>
                    </h4>

                    <div class="clearfix"></div>
                    <p v-if="plugin.description" class="text-muted">@{{{ plugin.description }}}</p>

                    <small v-if="plugin.author" class="text-light-gray text-xs">
                        @lang('plugins::core.detail.author'): <strong>@{{ plugin.author }}</strong>
                    </small>
                </td>

                <td class="plugin-status text-center">
                    @can('plugins::change_status')
                    <div>
                        <button v-on:click="changeStatus(plugin)"
                                v-bind:class="{'btn-danger': plugin.isActivated, 'btn-success': plugin.isInstallable }"
                                v-if="plugin.isInstallable"
                                class="change-status btn btn-default"
                                type="button"
                        >
                            <i class="fa fa-power-off"></i>
                        </button>
                    </div>
                    @endcan
                </td>
            </tr>
        </tbody>
    </table>

    <div v-if="!plugins.length" class="alert alert-info">
        <h4 class="no-margin">
            @lang('plugins::core.messages.no_plugins')
        </h4>
    </div>
</div>