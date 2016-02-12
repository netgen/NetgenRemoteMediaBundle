define(['remotemedia/view', './upload'], function(View, UploadView) {
    return View.extend(RemoteMediaShared.browser(UploadView));
});