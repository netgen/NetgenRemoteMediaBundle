define(['remotemedia/view', 'remotemedia/templates/tag'], function(View, Tag) {
    return View.extend(window.RemoteMediaShared.tagger(Tag));
});