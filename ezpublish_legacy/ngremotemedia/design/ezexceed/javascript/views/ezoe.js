define(['ngremotemedia/view','jquery-safe', '../models', './browser', './scaler'], function(View, $, Models, BrowserView, ScalerView){
  return View.extend(NgRemoteMediaShared.ezoe($, Models.attribute, BrowserView, ScalerView));
});