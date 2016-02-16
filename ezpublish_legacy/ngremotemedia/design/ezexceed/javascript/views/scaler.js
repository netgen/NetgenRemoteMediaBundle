define(['remotemedia/view', './scaled_version', 'jquery-safe', 'remotemedia/jcrop'], function(View, ScaledVersion, $) {
  return View.extend(RemoteMediaShared.scaler(ScaledVersion, $));
});