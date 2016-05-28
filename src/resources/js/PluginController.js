$(function() {
	new Vue({
		el: '#pluginsMap',
		ready: function() {
			this.loadPlugins();
		},
		data: {
			plugins: []
		},
		methods: {
			loadPlugins: function() {
				CMS.loader.show(this.$el);

				this.$http.get(Api.parseUrl('/api.plugins')).then(function (response) {
					this.plugins = response.data.content;
					CMS.loader.hide();
				});
			},
			trans: function(k, o) {
				return trans(k, o)
			},
			changeStatus: function (plugin) {
				if(plugin.isActivated) {
					var removeData = confirm(trans('plugins.core.messages.empty_database'));
				}

				this.$http.post(Api.parseUrl('/api.plugins'), {
					name: plugin.name,
					remove_data: removeData
				}).then(function (response) {
					this.loadPlugins();
				});
			}
		}
	});
});