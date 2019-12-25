var vueApp = new Vue({
	el: '#app',
	data: {
	    projectId: null,
	    localItems: [],
        newItemText: ""
	},
    computed: {
    },
	methods: {
	    error: function(message) {
            new Noty({
                type: 'error',
                text: msg,
                closeWith: ['click', 'button']
            }).show();
        },
        pull: function() {
            console.log("pull()");
            var that = this;
            $.post("ajax/pull", {
                projectId: this.projectId
            }).then(function(item) {
                that.localItems.push(item);
            }, function(reason) {
                that.error(reason);
            });
        },
        push: function(item) {
            console.log("push()");
            var that = this;
            return $.post("ajax/push", item).then(function() {
                that.discard(item);
            }, function(reason) {
                that.error(reason);
            });
        },
        discard: function(item) {
	        for(var i = 0; i < this.localItems.length; i++) {
	            if(this.localItems[i].id === item.id) {
	                this.localItems.splice(i, 1);
	                return;
                }
            }
        },
        submitNew: function() {
            console.log("submitNew()");
            var that = this;
            this.push({displayText: this.newItemText}).then(function() {
                that.newItemText = "";
            });
        }
	}
});
