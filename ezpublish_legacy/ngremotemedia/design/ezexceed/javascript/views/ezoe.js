define(['ngremotemedia/view','jquery-safe', '../models', './browser', './scaler'], function(View, $, Models, BrowserView, ScalerView){
  return View.extend(RemoteMediaShared.ezoe($, Models.attribute, BrowserView, ScalerView));
});