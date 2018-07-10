define([
    '../util/dom',
    '../util/obj',
    'jquery-1.9'
], function(dom, obj, $) {

    var getRecordIdFromSnippetWebComponent = function(elem) {
        var recordId = dom.getData(elem, 'record-id');
        if (!recordId) {
            var recordIdElement = elem.querySelector('record-id');
            recordId = recordIdElement && recordIdElement.innerHTML;
        }
        return recordId;
    };

    return {

        process: function(callback) {
            var elems = document.querySelectorAll('bbc-snippet');
            var recordIds = [];
            for (var i = 0; i < elems.length; ++i) {
                recordIds.push(getRecordIdFromSnippetWebComponent(elems[i]));
            }
            var self = this;

            if (recordIds.length > 0) {
                this.request(recordIds, function(snippetsItems) {
                    self.replace(snippetsItems);
                    callback();
                });
            }
        },

        request: function(ids, callback) {
            var url = '/programmes/snippet/' + encodeURIComponent(ids) + '.json';
            $.ajax({
                url: url,
                dataType: 'json',
                success: function (response) {
                    if (response) {
                        callback(response);
                    }
                },
                error: function (request, status, error) {
                    // console.log('Error when calling snippet URL: ' + url);
                }
            });
        },

        replace: function(snippets) {
            var index = {};
            for (var i = 0; i < snippets.length; ++i) {
                var container = document.createElement('div');
                container.innerHTML = snippets[i].html;
                var elem = container.children[0];
                index[snippets[i].id] = elem;
            }
            var elems = document.querySelectorAll('bbc-snippet');
            for (var i = 0; i < elems.length; ++i) {
                var recordId = getRecordIdFromSnippetWebComponent(elems[i]);
                if (recordId && index[recordId]) {
                    elems[i].parentElement.replaceChild(index[recordId].cloneNode(true), elems[i]);
                }
            }
        }
    };

});
