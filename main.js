var hatId = window.location.hash.substring(1) || prompt("Which hat?");

var vueApp = new Vue({
	el: '#app',
	data: {
	    hatId: hatId,
	    localItems: [],
        newItemText: ""
	},
    computed: {
    },
	methods: {
	    error: function(message) {
	        console.error(message);
            new Noty({
                type: 'error',
                text: message,
                closeWith: ['click', 'button']
            }).show();
        },
        errorResponse: function(response) {
            if(typeof response === 'string' || response instanceof String) {
                this.error(response);
            }
            else if(response instanceof Object) {
                this.error(response.responseText || ("Response with error code " + response.status));
            }
            else {
                this.error("Unknown error");
            }
        },
        pull: function() {
            console.log("pull()");
            var that = this;
            $.post("ajax/pull", {
                hatId: this.hatId
            }).then(function(itemString) {
                try {
                    var item = JSON.parse(itemString);
                    that.localItems.push(item);
                } catch(e) {
                    that.error("Badly formatted response from server");
                }
            }, function(reason) {
                that.errorResponse(reason);
            });
        },
        push: function(item) {
            console.log("push()");
            var that = this;
            return $.post("ajax/push", {hatId: this.hatId, displayText: item.displayText}).then(function() {
                that.discard(item);
            }, function(reason) {
                that.errorResponse(reason);
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
