var vueApp = new Vue({
	el: '#app',
	data: {
	    hatId: null,
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
            return $.post("ajax/push", item).then(function() {
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

function findJSONInResponse(response) {
    try {
        if(typeof response === 'string' || response instanceof String) {
            return JSON.parse(response);
        }
        else if(response instanceof Object) {
            if("responseJSON" in response) {
                return response.responseJSON;
            }
            else if("responseText" in response) {
                switch(response.status) {
                    case 413:
                        return {
                            error: [
                                "File upload or form data too large. Please try to upload a smaller file or submit the data in smaller chunks. "
                                + "If this problem seems to occur with reasonably sized data, please report this incident to an administrator."
                            ]
                        };
                    default:
                        return JSON.parse(response.responseText);
                }
            }
            else {
                return response;
            }
        }
        else {
            return {};
        }
    } catch (e) {
        return {
            error: [
                "Server encountered an unknown error or sent invalid response. Please report this incident to an administrator."
            ]
        }
    }
}
