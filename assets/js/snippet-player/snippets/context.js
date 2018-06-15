define(function() {

    /**
     * This UserGeo takes a single boolean flag indicating that
     * the user is inside the UK. If the flag is set to false
     * the user is said to be outside the UK.
     *
     * @param {null|boolean} uk
     * @constructor
     */
    function UserGeo(uk) {
        this._uk = uk;
    }

    /**
     * Flag indicating whether the user is within the UK.
     *
     * @returns {boolean}
     */
    UserGeo.prototype.isUk = function() {
        return this._uk === true;
    };

    /**
     * Flag indicating whether the user is outside the UK.
     *
     * @returns {boolean}
     */
    UserGeo.prototype.isNonUk = function() {
        return this._uk === false;
    };

    /**
     * Flag indicating whether the geo of the user should be
     * detected upstream. Will return true if neither inside
     * or outside of the UK is specified.
     *
     * @returns {boolean}
     */
    UserGeo.prototype.isAuto = function() {
        return this._uk == null;
    };

    /**
     * This PlaybackContext takes a context name as a string this
     * identifies the playback context and therefore the rules that
     * need to be applied server side to return the best audio. If
     * the context is not known name can be null, in which case
     * isEmpty will return true.
     *
     * @param {null|string} name
     * @constructor
     */
    function PlaybackContext(name) {
        this._name = name;
    }

    /**
     * Flag indicating whether the context is empty. Will return
     * true if the instance has not been provided a name.
     *
     * @returns {boolean}
     */
    PlaybackContext.prototype.isEmpty = function() {
        return this._name == null;
    };

    /**
     * The name of the playback context. Will return null if the
     * context has not been given a name.
     *
     * @returns {null|string}
     */
    PlaybackContext.prototype.getName = function() {
        return this._name;
    };

    return {
        UserGeo: UserGeo,
        PlaybackContext: PlaybackContext
    };

});
