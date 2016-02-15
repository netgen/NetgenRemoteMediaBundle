define(['remotemedia/view', './scaled_version', 'jquery-safe', 'jcrop'], function(View, ScaledVersion, $) {
        return View.extend(RemoteMediaShared.scaler(ScaledVersion, $));
    });