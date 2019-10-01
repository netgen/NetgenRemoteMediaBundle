if (!Array.prototype.difference) {
  Array.prototype.difference = function(other) {
    return this.filter(el => other.indexOf(el) < 0);
  };
}
