define(['ngremotemedia/view', './upload'], function(View, UploadView) {
    return View.extend(RemoteMediaShared.browser(UploadView));
});