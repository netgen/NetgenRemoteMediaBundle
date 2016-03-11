define(['ngremotemedia/view', 'jquery-safe', 'plupload/plupload'], function(View, $, plupload) {
    return View.extend(NgRemoteMediaShared.upload($, plupload));
});