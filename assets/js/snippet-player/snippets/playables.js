define(function() {

	function UrlAudioSource(src) {
		this._src = src;
	}

	UrlAudioSource.prototype.getSrc = function() {
		return this._src;
	};

	function ClipAudioSource(id) {
		this._id = id;
	}

	ClipAudioSource.prototype.getId = function() {
		return this._id;
	};

	return {
		UrlAudioSource: UrlAudioSource,
		ClipAudioSource: ClipAudioSource
	};

});
