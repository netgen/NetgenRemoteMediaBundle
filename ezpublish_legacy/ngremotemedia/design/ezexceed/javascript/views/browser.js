define(['ngremotemedia/view', './upload'], function(View, UploadView) {
    return View.extend(NgRemoteMediaShared.browser(UploadView));
});