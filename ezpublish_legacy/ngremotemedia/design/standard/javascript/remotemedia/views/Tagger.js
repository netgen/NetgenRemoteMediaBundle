/*globals RemoteMediaShared*/

var TagTemplate = _.template('<li><%-tag%><span class="remove"><a class="close" data-tag="<%-tag%>">x</a></span></li>');
RemoteMedia.views.Tagger = Backbone.View.extend(RemoteMediaShared.tagger(TagTemplate));